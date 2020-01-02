<?php

namespace App\Traits\Admin\CarePlan;
use App\Models\Admin\CarePlan\Barrier;
use App\Models\Admin\CarePlan\ContentDiscussed;

trait ContentDiscussedTrait
{
    public function saveContent($request)
    {
        $response = 0;
		if($request->id){
            $id = encrypt_decrypt('decrypt', $request->id);
            $tool = ContentDiscussed::findOrFail($id);
            $response = $tool->update($request->except('_token','id','submit_btn_type'));

            $request->session()->flash('message.content',trans('message.content_updated_successfully'));
        }
        else {
            $data = $request->except('_token','id','submit_btn_type');
            $response = ContentDiscussed::create($data);
            if($response){
				$tool_code = $this->generateContentID($response->id);
				$response = $response->update(['code' => $tool_code]);
			}
            $request->session()->flash('message.content',trans('message.content_added_successfully'));
        }
        return $response;
    }

    //Function to generate Barrier ID
    public function generateContentID($id)
    {
		return ContentDiscussed::CONTENT_PREFIX.$id;
    }
}



?>
