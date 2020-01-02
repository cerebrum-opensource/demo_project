<?php

namespace App\Traits\Admin\CarePlan;
use App\Models\Admin\CarePlan\Tool;
use Illuminate\Support\Facades\Storage;

trait ToolTrait
{
    public function saveTool($request)
    {
        $response = 0;
		if($request->id){
            $id = encrypt_decrypt('decrypt', $request->id);
            $tool = Tool::find($id);
            $data = $this->getFilteredPostData($request);

            $response = $tool->update($data);
            $request->session()->flash('message.content',trans('message.tool_updated_successfully'));
        }
        else {
            $data = $this->getFilteredPostData($request);
            $data['status'] = Tool::ACTIVE;
            $response = Tool::create($data);  
            if($response){
				$tool_code = $this->generateToolID($response->id);
				$response = $response->update(['code' => $tool_code]);
				//$response = 1;
			}
            //$response = 1;
            $request->session()->flash('message.content',trans('message.tool_added_successfully'));
        }
        return $response;
    }

    //Function to generate Diagnosis ID
    public function generateToolID($id)
    {
		return Tool::TOOL_PREFIX.$id;
    }

    public function getFilteredPostData($request)
    {
        $data = $request->except('_token','id','submit_btn_type');

        if($data['type'] === TOOL::TOOL_TYPE_ONLINE) {
            $data['location'] = $data['link'];
            $data['file_path'] ='';
        }

        if($data['type'] === TOOL::TOOL_TYPE_PDF) {
            if($request->hasFile('file_path'))
            {
                $fileName = time().'.'.request()->file_path->getClientOriginalExtension();
                Storage::disk('s3')->put(config('filesystems.s3_admin_tool_partial_path').'/'.$fileName, file_get_contents($request->file('file_path')),'public');
                $data['file_path'] = $fileName;
                $data['location'] = '';
            }
          //  unset($data['file_path']);
        }
 
        unset($data['link']);
        unset($data['is_upload']);
        return $data;
    }
}



?>
