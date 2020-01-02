<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Traits\RegistryTrait;
use Yajra\DataTables\Html\Builder;
use App\Models\Registry;
use App\Models\State;
use App\Http\Requests\RegistryRequest;
use Auth;

class RegistryController extends Controller {
  use RegistryTrait;

  public $headings = array();

  public function __construct() {
    $this->middleware(['auth', 'isAdmin', 'prevent-back-history']);
    
    //this array is to show heading of listing according to type
    $this->headings = [
        'contract_payers' => 'Contract Payer',
        'insurances' => 'Insurance',
        'pcp_informations' => 'PCP Information',
        'referral_sources' => 'Referral Source',
        'emergency_departments' => 'Emergency Department',
        'rehabs' => 'Rehab Information',
        'hospice_providers' => 'Hospice Provider',
        'specialities' => 'Specialties',
        'housing_assistances' => 'Housing Assistance',
        'mental_health_assistances' => 'Mental Health Assistance',
        'home_health_providers' => 'Home Health Provider'
    ];
  }
  
  /**
   * Displays datatables front end view
   *
   * @return \Illuminate\View\View
  */
  public function getIndex(Request $request, Builder $htmlBuilder)
  {
      //when there is ajax request then wwe will return records
      if ($request->ajax()) {
          return datatables()
                  ->eloquent($this->query())
                  ->addIndexColumn()
                  ->editColumn('email', function($setting) {
                    return !empty($setting->email) ? $setting->email : '-';
                  })
                  ->editColumn('contact_name', function($setting) {
                    return !empty($setting->contact_name) ? $setting->contact_name : '-';
                  })
                  ->editColumn('city', function($setting) {
                    $address = '';
                    if(!empty($setting->address_line1)){
                      $address = $setting->address_line1.' ,';
                    }
                    if(!empty($setting->address_line2)){
                      $address.= $setting->address_line2.' ,';
                    }
                    if(!empty($setting->city)){
                      $address.= $setting->city.' ,';
                    }
                    if(!empty($setting->state_name)){
                      $address.= $setting->state_name;
                    }
                    return $address;
                  })
                  ->editColumn('contact_email', function($setting) {
                    return !empty($setting->contact_email) ? $setting->contact_email : '-';
                  })
                  ->editColumn('code', function($setting) {
                    return !empty($setting->code) ? $setting->code : '-';
                  })
                  ->editColumn('web_address', function($setting) {
                    return !empty($setting->web_address) ? $setting->web_address : '-';
                  })
                  ->editColumn('org_name', function($setting) {
                    return !empty($setting->org_name) ? $setting->org_name : '-';
                  })
                  ->editColumn('contact_title', function($setting) {
                    return !empty($setting->contact_title) ? $setting->contact_title : '-';
                  })
                  ->addColumn('action', function ($setting) {
                      return '<a href="'.route("edit_existing_registry", [$setting->type,encrypt_decrypt('encrypt',$setting->id)]) .'"  class="" title="Edit record" style="color:orange"><i class="fa fa-pencil"></i></a>
                          <a style="color:red" href="#" data-id="'.encrypt_decrypt('encrypt',$setting->id).'"  data-model="Registry" title="Delete" class="delete_model_by_id">
                       <i class="fa fa-trash" aria-hidden="true"></i>
                      </a>';
                    })
                  ->make(true);
      }

      $htmlBuilder->table(['class' => 'table table-striped  table-bordered table-hover table-align-left datatable loader_div dataTable no-footer']);

      $htmlBuilder->parameters(['bSort' => false,'language' => ['searchPlaceholder' => $request->type == 'emergency_departments' ? trans('label.search_by_Org_Name') : trans('label.search_by_Org_Name_Email')]]);
      $dataTable = $htmlBuilder->columns($this->getColumns());
      return view('admin.registries.index', compact('dataTable'))->with('active', 'settings')->with('sub_active', $request->type)->with('type', $request->type)->with('type_heading', $this->headings[$request->type]);
  }  

  /**
   * Return Add form 
   *
   * @return \Illuminate\View\View
  */
  public function getCreate(Request $request)
  {
      $states = State::all()->pluck('full_name','id')->prepend('Please select', '')->toArray();
      $model = new Registry();
      return view('admin.registries.form', compact('model', 'states'))->with('active', 'settings')->with('sub_active', $request->type)->with('type', $request->type)->with('type_heading', $this->headings[$request->type]);
  }


  public function postCreate(RegistryRequest $request)
  {
      if($request->type == 'contract_payers')
      {
        $start_date  = change_date_format($request->effective_start_date);
        $request->request->add(['effective_start_date'=>$start_date]);
        $end_date  = change_date_format($request->effective_end_date);
        $request->request->add(['effective_end_date'=>$end_date]);
      }
      
      $data= $request->except('_token','id', 'model_id');
      
      $data['user_id']=Auth::id();
      $response = Registry::create($data);  
      
      $request->session()->flash('message.content', $this->headings[$request->type].' '.trans('message.added_successfully'));
    
    if($response)
    {
        $request->session()->flash('message.level','success');
        return redirect()->route('registries',[$request->input('type')]);
    }
    else
    {
        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content',trans('message.some_error').$message.'.');
        return redirect()->route('registries',[$request->input('type')]);
    }    
  }

  /**
   * Return Edit form with values
   *
   * @return \Illuminate\View\View
  */
  public function getEdit($type, $id, Request $request)
  {
      $states = State::all()->pluck('full_name','id')->prepend('Please select', '')->toArray();
      $id = encrypt_decrypt('decrypt', $id);
      $model = Registry::findOrFail($id);
      
      return view('admin.registries.form', compact('model', 'states'))->with('active', 'settings')->with('sub_active', $request->type)->with('type', $request->type)->with('type_heading', $this->headings[$request->type]);
  }

  public function postEdit($type, $id, RegistryRequest $request)
  {
      $id = encrypt_decrypt('decrypt',$id);

      if($request->type == 'contract_payers')
      {
        $start_date  = change_date_format($request->effective_start_date);
        $request->request->add(['effective_start_date'=>$start_date]);
        $end_date  = change_date_format($request->effective_end_date);
        $request->request->add(['effective_end_date'=>$end_date]);
      }
      $data= $request->except('_token','id', 'model_id');
      
      $data['user_id']=Auth::id();
      $response = Registry::find($id)->update($data);  
      
      $request->session()->flash('message.content',$this->headings[$request->type].' '.trans('message.updated_successfully'));
    
    if($response)
    {
        $request->session()->flash('message.level','success');
        return redirect()->route('registries',[$request->input('type')]);
    }
    else
    {
        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content',trans('message.some_error').$message.'.');
        return redirect()->route('registries',[$request->input('type')]);
    }    
  }

  /* Delete Method to soft delete the data */
  public function deleteById(Request $request)
  {

    // emergency and specialst pending
    if ($request->has('id') && $request->has('model'))
    {
        try
        {
            $id = encrypt_decrypt('decrypt',$request->get('id'));
        } 
        catch (\Exception $e)
        {
             $request->session()->flash('message.level','danger');
             $request->session()->flash('message.content','Some error while delete record.');
             exit;
        }

        $model='';
        $value=$request->input('model');
        $type=$request->input('type');
        switch ($value) 
        {
        case 'ManageableField':
            $model = \App\Models\ManageableField::find($id)->delete();
            break;
        case 'Registry':
            if(\App\Models\Patient::where('referral_source', $id)->exists()){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Referral Source is already assigned to a patient.');
            }
            elseif(\App\Models\Patient::where('rehab_information_id', $id)->exists() &&  $type == 'rehabs'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Rehab Information is already assigned to a patient.');
            }
            elseif(\App\Models\Patient::where('pcp_id', $id)->exists() &&  $type == 'pcp'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Pcp Information is already assigned to a patient.');
            }
            else if(\App\Models\Patient::where('specialist_id', 'like', "%\"{$id}\"%")->exists() &&  $type == 'speciality'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Specialty is already assigned to a patient.');
            }
            elseif(\App\Models\Patient::where('contract_payer', $id)->exists()){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Contract payer is already assigned to a patient.');
            }
            else if(\App\Models\Patient::where('home_health_provider_id', $id)->exists() &&  $type == 'home_health_providers'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Home Health Provider is already assigned to a patient.');
            }
            else if(\App\Models\Patient::where('mental_health_assistance_id', $id)->exists() &&  $type == 'mental_health_assistances'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Mental Health Assistance is already assigned to a patient.');
            }
            else if(\App\Models\Patient::where('housing_assistance_id', $id)->exists() &&  $type == 'housing_assistances'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Housing Assistance is already assigned to a patient.');
            }
            else if(\App\Models\Patient::where('hospice_provider_id', $id)->exists() &&  $type == 'hospice_providers'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Hospice Provider is already assigned to a patient.');
            }
            else if(\App\Models\PatientInsurance::where('insurance_id', $id)->exists()  &&  $type == 'insurances'){
              $model = 2;
              $request->session()->flash('message.level','danger');
              $request->session()->flash('message.content','This Insurance is already assigned to a patient.');
            }
            else {
              $model = \App\Models\Registry::find($id)->delete();
              $request->session()->flash('message.level','success');
              $request->session()->flash('message.content','Record Deleted successfully.');
            }
            break;

            case 'Goal':

                $model = \App\Models\Admin\CarePlan\Goal::find($id);
                if ($model->hasCareplanGoal()) {
                    $request->session()->flash('message.level','danger');
                    $request->session()->flash('message.content','Goal cannot be deleted because goal is associated with a careplan.');
                } else {
                    $model->delete();
                    $request->session()->flash('message.level','success');
                    $request->session()->flash('message.content','Goal deleted successfully.');
                }

                break;

            case 'Diagnosis':
                $model = \App\Models\Admin\CarePlan\Diagnosis::find($id);
                if (!$model->hasGoal()) {
                    $model->delete();
                    $request->session()->flash('message.level','success');
                    $request->session()->flash('message.content','Diagnosis deleted successfully.');
                } else {
                    $request->session()->flash('message.level','danger');
                    $request->session()->flash('message.content','Diagnosis cannot be deleted because diagnosis is associated with a goal.');
                }
                break;
            case 'Tool':
                $model = \App\Models\Admin\CarePlan\Tool::find($id);

                if (!$model->hasGoal()) {
                    $model->delete();
                    $request->session()->flash('message.level','success');
                    $request->session()->flash('message.content','Tool deleted successfully.');
                } else {
                    $request->session()->flash('message.level','danger');
                    $request->session()->flash('message.content','Tool cannot be deleted because tool is associated with a goal.');
                }

                break;

            case 'Barrier':
                $model = \App\Models\Admin\CarePlan\Barrier::find($id);
                if (!$model->hasGoal()) {

                    if($model->isAssessmentBarrier()) {
                        $request->session()->flash('message.level','danger');
                        $request->session()->flash('message.content','Barrier cannot be deleted because barrier is associated with a careplan assessment.');
                    } else {
                        $model->delete();
                        $request->session()->flash('message.level','success');
                        $request->session()->flash('message.content','Barrier deleted successfully.');
                    }

                } else {
                    $request->session()->flash('message.level','danger');
                    $request->session()->flash('message.content','Barrier cannot be deleted because barrier is associated with a goal.');
                }

                break;

            case 'Content':
                $model = \App\Models\Admin\CarePlan\ContentDiscussed::destroy($id);
                $request->session()->flash('message.level','success');
                $request->session()->flash('message.content','Content deleted successfully.');

                break;
          default:
            $model = 0;
            break;
        }

        if($model)
        {
             return response()->json(['message'=>'Record Deleted successfully.','status'=>1],200);
        }     
        else 
        {
            $request->session()->flash('message.level','danger');
            $request->session()->flash('message.content','Some error while delete record.');
            return response()->json(['message'=>'Some error while delete record.','status'=>0],200);
        }             
    }
    else {
        $request->session()->flash('message.level','danger');
        $request->session()->flash('message.content','Some error while delete record.');
        return response()->json(['message'=>'','status'=>0],200);

    }        
  }
}