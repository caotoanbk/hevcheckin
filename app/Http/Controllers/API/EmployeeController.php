<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Employee;
use App\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return Employee::orderBy('created_at', 'desc')->paginate(15);

        $type = $request->get('type');

        if($type == 'allocated'){
            return Employee::whereNotNull('EmployeeCardname')->orderBy('created_at', 'desc')->paginate(15);
        }

        if($type == 'avaiable'){
            return Employee::whereNull('EmployeeCardname')->orderBy('created_at', 'desc')->paginate(15);
        }

        return Employee::orderBy('created_at', 'desc')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'EmployeeName' => 'required|string|max:191',
            'EmployeeIdentity' => 'required|string|max:191|unique:employees',
            // 'EmployeeCardname' => 'sometimes|string|min:6|unique:employees',
            'EmployeePhoto' => 'required'
        ]);
        if($request->EmployeePhoto){
            $name = time().'.'.explode('/', explode(':', substr($request->EmployeePhoto, 0, strpos($request->EmployeePhoto, ';')))[1])[1];

            \Image::make($request->EmployeePhoto)->save(public_path('img/profile/').$name);
            $request->merge(['EmployeePhoto' => $name]);
        }


        $newEmployee = Employee::create([
            'EmployeeName' => $request['EmployeeName'],
            'EmployeeInfo' => $request['EmployeeInfo'],
            'EmployeeType' => $request['EmployeeType'],
            'EmployeeIdentity' => $request['EmployeeIdentity'],
            'EmployeeCardname' => $request['EmployeeCardname'],
            'EmployeePhoto' => $request['EmployeePhoto'],
        ]);

        if($request['EmployeeCardname']){
            $card = Card::where('CardName', $request['EmployeeCardname'])->first();
            if($card){                
                $card->EmployeeIdentity = $newEmployee->EmployeeIdentity;
                $card->save();
            }
        }

        return $newEmployee;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        $this->validate($request,[
            'EmployeeName' => 'required|string|max:191',
            'EmployeeIdentity' => 'required|string|max:191|unique:employees,EmployeeIdentity,'.$employee->id,
            'EmployeeType' => 'required',
            'EmployeePhoto' => 'required'
        ]);

        $currentPhoto = $employee->EmployeePhoto;
        if($request->EmployeePhoto != $currentPhoto){
            $name = time().'.'.explode('/', explode(':', substr($request->EmployeePhoto, 0, strpos($request->EmployeePhoto, ';')))[1])[1];

            \Image::make($request->EmployeePhoto)->save(public_path('img/profile/').$name);
            $request->merge(['EmployeePhoto' => $name]);

            $employeePhoto = public_path('img/profile/').$currentPhoto;

            if(file_exists($employeePhoto)){
                @unlink($employeePhoto);
            }
        }

        if($employee && ($employee->EmployeeCardname != $request->EmployeeCardname)){

            $oldCard = Card::where('CardName', $employee->EmployeeCardname)->first();
            if($oldCard){
               $oldCard->EmployeeIdentity = null;
               $oldCard->save();
            }

            $newCard = Card::where('CardName', $request['EmployeeCardname'])->first();
            if($newCard){
                $newCard->EmployeeIdentity = $employee->EmployeeIdentity;
                $newCard->save();
            }
        }

        $employee->update($request->all());

        return ['message' => 'Updated the employee info'];
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if($employee->EmployeeCardname){
            $card = Card::where('CardName', $employee->EmployeeCardname)->first();
            $card->EmployeeIdentity = null;
            $card->save();
        }

        $employeePhoto = public_path('img/profile/').$employee->EmployeePhoto;

        if(file_exists($employeePhoto)){
            @unlink($employeePhoto);
        }

        // delete the card

        $employee->delete();

        return ['message' => 'Employee Deleted'];
    }

    public function getCardOptions(){
        return Card::whereNull('EmployeeIdentity')->orderBy('created_at', 'desc')->get();
    }


    public function getCardOptionsEdit($id){

        $employee = Employee::findOrFail($id);

        $cards = Card::where('EmployeeIdentity', null);

        if($employee->EmployeeCardname){
            $cards = $cards->orWhere('CardName', $employee->EmployeeCardname);
        }

        return $cards->orderBy('created_at', 'desc')->get();
    }

    public function search() {
        $type = \Request::get('type') ? \Request::get('type') : '';

        if($type == 'allocated'){
            $employees = Employee::whereNotNull('EmployeeCardname');
        } else if($type == 'avaiable'){
            $employees = Employee::whereNull('EmployeeCardname');
        } else{
            $employees = Employee::select();
        }

        if ($search = \Request::get('q')) {
            $employees = $employees->where(function($query) use ($search){
                $query->where('EmployeeName', 'LIKE', "%$search%");
                $query->orWhere('EmployeeIdentity', 'LIKE', "%$search%");
                $query->orWhere('EmployeeInfo', 'LIKE', "%$search%");
            });
        }else{
            $employees = $employees->orderBy('created_at', 'desc');
        }
        return $employees->with('card')->paginate(15);
    }
}
