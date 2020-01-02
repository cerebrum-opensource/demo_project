<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient as PatientRequest; 
use App\Http\Requests\PatientAssessment as PatientAssessmentRequest; 
use App\Http\Requests\PatientReject as PatientRejectRequest; 
use App\Http\Requests\PatientAssessmentComplete as PatientAssessmentCompleteRequest; 
use App\Models\ManageableField;
use App\Models\Registry;
use App\Models\ContractPayer;
use App\Models\PatientData;
use App\Models\ReferralSource;
use App\Models\PcpInformation;
use App\Models\State;
use App\Models\PatientAssessment;
use App\Models\IcdCode;
use App\Models\Admin\CarePlan\ContentDiscussed;
use Illuminate\Contracts\Encryption\DecryptException;
use View;
use Helper;
// use Illuminate\Support\Facades\Session;
use App\Models\User;
use Storage;
use Auth;


class CommonController extends Controller
{
	
	public function __construct(){
		$this->middleware(function ($request, $next) {
            if(Auth::user()->status != 1)
            {
                Auth::logout();
                return redirect('/login');
            }
            return $next($request);
        });
	}
    

    // get the list of the document and notes
    public function getDocumentList(Request $request)
    {
        
        if ($request->ajax()) {
            if($request->input('id')){
               $id=$request->input('id');
            }
            $patient = Patient::find($id);
            if($request->input('tab_name') == 'notes')
            {
                $patient_notes = $patient->patient_notes()->paginate(PAGINATION_COUNT_10);
                return view('patients.common.notes_table', ['patient_notes' => $patient_notes,'patient' => $patient])->render();  
            }
            else
            {
                $patient_docs = $patient->patient_docs()->paginate(PAGINATION_COUNT_10);
                return view('patients.common.documents_table', ['patient_docs' => $patient_docs,'patient' => $patient])->render();
            }
        }  
        
    }    

    public function postCreate(PatientRequest $request)
    {
        $request->session()->reflash();
        $message='';
        if($request->step_number == 4){
             $response = self::savePatientDocumentTabData($request);
             $message='Document';
        }
           
        if($request->step_number == 5){
            $response = self::savePatientNotesTabData($request); 
            $message='Note';    
        }
                 
        if($response)
        {
            $request->session()->flash('message.'.$message.'-level','success');
            $request->session()->flash('message.content',''. $message.trans('message.added_successfully'));
            return response()->json(['message'=>''. $message.' added successfully.','patient_id'=>$response],200);
        }
        else
        {
            $request->session()->flash('message.'.$message.'-level','danger');
            $request->session()->flash('message.content',trans('message.some_error'). $message.'.');
            return response()->json(['message'=>'Some error while added new '. $message.'.'],200);            
        }
    }    

    
    public function savePatientNotesTabData($request)
    {
        DB::beginTransaction();
        $patient_id = $request->patient_id;                 
        $patient_data= new PatientData();
        $patient = Patient::find($patient_id);
        $patient_data->calc($patient->random_key);
        $patient_data->fill(['patient_id'=>$patient_id,'user_id'=>Auth::user()->id,'type'=>'notes','name'=>$request->notes_area,'value'=>$request->notes_subject,'status'=>1]);
            
        if($patient_data->save())
        {
            DB::commit();
            return $patient_id;
        }
        DB::rollBack();
        return false;
    }     

    public function savePatientDocumentTabData($request)
    {
        DB::beginTransaction();
        $patient_id = $request->patient_id;                 
        $patient_data= new PatientData();
        $patient = Patient::find($patient_id);
        $patient_data->calc($patient->random_key);

        //move document to local server directory
        $document_name = time().'.'.request()->uploaded_document->getClientOriginalExtension();

        //request()->uploaded_document->move(public_path('documents/patients'), $document_name);
        Storage::disk('s3')->put(config('filesystems.s3_patient_documents_partial_path').$patient_id.'/'.$document_name, file_get_contents($request->file('uploaded_document')),'public');

        $patient_data->fill(['patient_id'=>$patient_id,'user_id'=>Auth::user()->id,'type'=>'document','category_id'=>$request->category_id,'name'=>$request->document_name,'value'=>$document_name,'status'=>1]);
            
        if($patient_data->save())
        {
            DB::commit();
            return $patient_id;
        }

        DB::rollBack();
        return false;
    }

    public function deletePatientDocument(Request $request)
    {
       
        try{
            $id = \Crypt::decrypt($request->doc_id);
            $patient_id = \Crypt::decrypt($request->patient_id);      
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        DB::beginTransaction();
        DB::table('patient_data')
            ->where('id', $id)
            ->update(['comment'=>$request->reason,'deleted_by'=>Auth::Id()]);
        $patient_data = PatientData::find($id);
        $patient = Patient::find($patient_id);
        $patient_data->calc($patient->random_key);
        $document_name = $patient_data->value;
        $filePath = config('filesystems.s3_patient_documents_partial_path').$patient_id.'/'.$document_name;
       
       // Storage::disk('s3')->delete($filePath);
        if($patient_data->delete())
        {
            DB::commit();
            $request->session()->flash('message.Document-level','success');
            $request->session()->flash('message.content',trans('message.document_deleted'));
            return response()->json(['message'=>trans('message.document_deleted'),'patient_id'=>$patient_id],200);
           // return $patient_id;
        }
        else {
            DB::rollBack();
            $request->session()->flash('message.Document-level','danger');
            $request->session()->flash('message.content',trans('message.error_document_deleted'));
            return response()->json(['message'=>trans('message.error_document_deleted')],200);        
        }
    }
    

    // Get documents individually
    
    public function getDocsList($role,$patient_id,Request $request){

        try{           
            $patient_id = \Crypt::decrypt($patient_id);
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        $request->session()->reflash();
        $active = 'patient_registrations';
        $active_tab = "patient_registrations"; 
        $active_step = "patient_registrations";   
        $patient = Patient::find($patient_id);
        $patient->calc($patient->random_key); 
        $patient_docs = [];  
        $patient_docs = $patient->patient_docs()->paginate(PAGINATION_COUNT_10); 
        $doc_categories = ManageableField::where('type','document_category')->pluck('name','id')->prepend('Select a document category', '');

        return view('patients.registration.documents',compact('active', 'active_step', 'active_tab', 'patient','patient_docs','doc_categories'));       
    }

    // Get notes individually

    public function getNotesList($role,$patient_id,Request $request){
        try{           
            $patient_id = \Crypt::decrypt($patient_id);
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        $active = 'patient_registrations';
        $active_tab = "patient_registrations"; 
        $active_step = "patient_registrations";   
        $patient = Patient::find($patient_id);
        $patient->calc($patient->random_key);
        $patient_notes = [];    
        $patient_notes = $patient->patient_notes()->paginate(PAGINATION_COUNT_10); 
        return view('patients.registration.notes',compact('active', 'active_step', 'active_tab', 'patient','patient_notes'));       
    }
    
    // function to add assessment for patient

    public function postCreateAssessment(PatientAssessmentRequest $request)
    {   
        try{
            $patient_id = \Crypt::decrypt($request->patient_id);      
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        DB::beginTransaction();
      //  $patient_id = $request->patient_id;                 
        $patient_assessment= new PatientAssessment();
        $patient = Patient::find($patient_id);
        $patient_assessment->fill(['patient_id'=>$patient_id,'type_id'=>Auth::user()->id,'comment_type'=>'assessment','user_type'=>Auth::user()->getRoleNames()[0],'comment'=>$request->comment,'status'=>1]);
            
        if($patient_assessment->save())
        {
            DB::commit();
           $request->session()->flash('message.level','success');
           $request->session()->flash('message.content',trans('message.comment_added'));
            return response()->json(['message'=>trans('message.comment_added'),'patient_id'=>$patient_id],200);
        }

        DB::rollBack();
        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content',trans('message.error_comment_added'));
        return response()->json(['message'=> trans('message.error_comment_added')],200);  
    }

     // function to reject patient

    public function postRejectPatient(PatientRejectRequest $request)
    {
        try{
            $patient_id = \Crypt::decrypt($request->patient_id);      
        } catch (DecryptException $e) {
            abort(404);
            exit;
        }
        
        DB::beginTransaction();              
        
        if ($request->has('patient_id') && $request->input('patient_id') !='') 
        {
            $patient = Patient::find(decrypt($request->input('patient_id')));
            $patient->calc($patient->random_key);
            $role = Auth::user()->getRoleNames()[0];
            
            if($role == 'CM'){
                $patient->cm_case_status = '3';   
            }
            if($role == 'MD'){
                $patient->md_case_status = '3';   
            }

            $patient->registration_status = '3';   
        }

        // added reject comment in table
        if ($request->has('reason') && $request->input('reason') !='') {
            $patient_assessment= new PatientAssessment();
            $patient_assessment->fill(['patient_id'=>$patient_id,'type_id'=>Auth::user()->id,'comment_type'=>'assessment_rejection','comment'=>$request->reason,'status'=>1]);

        }
        if($patient->save() && $patient_assessment->save())
        {
            DB::commit();
           $request->session()->flash('message.level','success');
           $request->session()->flash('message.content',trans('message.patient_reject'));
            return response()->json(['message'=>trans('message.patient_reject'),'patient_id'=>$patient_id],200);
        }

        DB::rollBack();
        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content',trans('message.error_patient_reject'));
        return response()->json(['message'=> trans('message.error_patient_reject')],200);  
    }


    // get the list of the document and notes
    public function getAssessmentList(Request $request)
    {
        $patient_assessments = [];  
        if ($request->ajax()) {
            if($request->input('id')){
               $id=$request->input('id');
            }
            $patient = Patient::find($id);
            $patient_assessment_comments = $patient->patient_assessment_comment()->get(); 
            return view('patients.common.assessment_comments', ['patient_assessment_comments' => $patient_assessment_comments,'patient' => $patient])->render(); 
        }  
        
    }

    
    // get the patient deatail
    
    public function getPatientView(Request $request,Patient $patient)
    {  
        $data = [];

        if ($request->ajax()) {
           // echo ;
            if ($request->has('id') && $request->input('id') !='') {
                    try{
                        $patient_id = \Crypt::decrypt($request->input('id')); 
                    }     
                    catch (DecryptException $e) {
                        abort(404);
                        exit;
                    }
                    $patient = Patient::find($patient_id);
                    $patient->calc($patient->random_key);
            }
            $data = $patient;
        }
        return View::make('patients.patient_view_modal')->with('patient', $data);
    }   
        
    // mark assessment complete of current login user type    
    public function getCompleteAssessment(PatientAssessmentCompleteRequest $request)
    {  
        if ($request->ajax()) {
            if ($request->has('patient_id') && $request->input('patient_id') !='') {
                    $patient = Patient::find(decrypt($request->input('patient_id')));
                    $patient->calc($patient->random_key);

                    $role = Auth::user()->getRoleNames()[0];
                    if($role == 'CM'){

                        //check if patient previous data is filled and it is in-complete before making it complete
                        if($patient->cm_tab_completed >= 2 && $patient->cm_case_status >= 1){
                            //set assessment as completed
                            $patient->cm_case_status = '2';
                            $patient->cm_complete_assessment_at =  \Carbon\Carbon::now();

                            //check if other have also completed assessment then mark patient registration status as complete
                            if($patient->md_case_status == '2' && $patient->chw_case_status == '2' && $patient->patient_decision == 'accepts')
                                $patient->registration_status = '2';
                            
                            $patient->save();
                        }
                    }                    
                    elseif($role == 'MD'){

                        //check if patient previous data is filled and it is in-complete before making it complete
                        if($patient->md_tab_completed >= 1 && $patient->md_case_status >= 1){
                            //set assessment as completed
                            $patient->md_case_status = '2';
                            $patient->md_complete_assessment_at =  \Carbon\Carbon::now();

                            //check if other have also completed assessment then mark patient registration status as complete
                            if($patient->cm_case_status == '2' && $patient->chw_case_status == '2' && $patient->patient_decision == 'accepts')
                                $patient->registration_status = '2';

                            $patient->save();
                        }
                    }
                    elseif($role == 'CHW'){

                        //check if patient previous data is filled and it is in-complete before making it complete
                        if($patient->chw_tab_completed >= 3 && $patient->chw_case_status >= 1){
                            //set assessment as completed
                            $patient->chw_case_status = '2';
                            $patient->chw_complete_assessment_at =  \Carbon\Carbon::now();

                            //check if other have also completed assessment then mark patient registration status as complete
                            if($patient->cm_case_status == '2' && $patient->md_case_status == '2' && $patient->patient_decision == 'accepts')
                                $patient->registration_status = '2';
                            
                            $patient->save();
                        }
                    }
            }



            $request->session()->flash('message.level','success');
            $request->session()->flash('message.content',trans('message.patient_assessment'));

            return response()->json(['message'=>trans('message.patient_assessment')],200);
        }
    }   


    public function updatePasswordExpiry(Request $request)
    {
        $user = \Auth::user();
        User::find($user->id)->update(['password_expiry' => \Carbon\Carbon::now()->addDays(15)]);
        $request->session()->forget('password_expire_time');
        return response()->json(['success' => true], 200);
    }
    
    public function postIcdCode(Request $request)
    {  
        $icd_codes = [];

        if ($request->has('data') && $request->input('data') !='') {
            if($request->selected_code){
                $icd_codes = IcdCode::where(function($q) use($request){ 
                                                $q->orWhere('code', 'like', $request->input('data') . '%')
                                                  ->orWhere('name', 'like', '%'.$request->input('data').'%');
                                            })->whereNotIn('id', $request->selected_code)->limit(100)->get();   
            }
            else{
               $icd_codes = IcdCode::where(function($q) use($request){ 
                                                $q->orWhere('code', 'like', $request->input('data') . '%')
                                                  ->orWhere('name', 'like', '%'.$request->input('data').'%');
                                            })->limit(100)->get();  
            }

            //$icd_codes = IcdCode::where('code', 'like', $request->input('data') . '%')->get();  
 
            return response()->json(['html' => $icd_codes], 200);

        }
        else if($request->has('data') && $request->input('data') !=''){
			$icd_codes = IcdCode::where('code', 'like', $request->input('data') . '%')->get();
			return response()->json(['html' => $icd_codes], 200);
		}
        else {
             return response()->json(['html' => $icd_codes], 200);
        }
    }


    public function postContentDiscussed(Request $request)
    {  
        $content_discussed = [];

        if ($request->has('data') && $request->input('data') !='') {
            if($request->selected_code){
                $content_discussed = ContentDiscussed::where(function($q) use($request){ 
                                                $q->orWhere('code', 'like', $request->input('data') . '%')
                                                  ->orWhere('title', 'like', '%'.$request->input('data').'%');
                                            })->whereNotIn('id', $request->selected_code)->limit(100)->get();   
            }
            else{
               $content_discussed = ContentDiscussed::where(function($q) use($request){ 
                                                $q->orWhere('code', 'like', $request->input('data') . '%')
                                                  ->orWhere('title', 'like', '%'.$request->input('data').'%');
                                            })->limit(100)->get();  
            }
 
            return response()->json(['html' => $content_discussed], 200);

        }
        else if($request->has('data') && $request->input('data') !=''){
            $content_discussed = ContentDiscussed::where('code', 'like', $request->input('data') . '%')->get();
            return response()->json(['html' => $content_discussed], 200);
        }
        else {
             return response()->json(['html' => $content_discussed], 200);
        }
    }   
}
