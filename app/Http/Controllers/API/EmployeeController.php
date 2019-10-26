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
    public function index()
    {
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
                $card->employee_id = $newEmployee->id;
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
        //
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
            $card->employee_id = null;
            $card->save();
        }

        // delete the card

        $employee->delete();

        return ['message' => 'Employee Deleted'];
    }

    public function getCardOptions(){
        return Card::whereNull('employee_id')->orderBy('created_at', 'desc')->get();
    }
}
