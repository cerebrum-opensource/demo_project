<?php

namespace App\Traits\Admin\CarePlan;
use App\Models\Admin\CarePlan\Barrier;

trait BarrierTrait
{
    public function saveBarrier($request)
    {
        $response = 0;
		if($request->id){
            $id = encrypt_decrypt('decrypt', $request->id);
            $tool = Barrier::find($id);  
            $data= $request->except('_token','id','submit_btn_type');
            $response = $tool->update($data);
            $request->session()->flash('message.content',trans('message.barrier_updated_successfully'));
        }
        else {
            $data = $request->except('_token','id','submit_btn_type');
            $data['status'] = Barrier::ACTIVE;
            $response = Barrier::create($data);  
            if($response){
				$tool_code = $this->generateBarrierID($response->id);
				$response = $response->update(['code' => $tool_code]);
			}
            $request->session()->flash('message.content',trans('message.barrier_added_successfully'));
        }
        return $response;
    }

    //Function to generate Barrier ID
    public function generateBarrierID($id)
    {
		return Barrier::BARRIER_PREFIX.$id;
    }
}



?>
