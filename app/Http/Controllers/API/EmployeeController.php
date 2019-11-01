<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Employee;
use App\Card;
use App\History;
use App\Supplier;
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

        if(auth()->user()->supplier_id){
            $employees = Employee::where('user_id', auth()->id());
        } else {
            $employees = Employee::select();
        }

        if($type == 'allocated'){
            return $employees->whereNotNull('EmployeeCardname')->orderBy('created_at', 'desc')->paginate(15);
        }

        if($type == 'avaiable'){
            return $employees->whereNull('EmployeeCardname')->orderBy('created_at', 'desc')->paginate(15);
        }

        return $employees->orderBy('created_at', 'desc')->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(auth()->user()->supplier_id){
            $supplierName = Supplier::find(auth()->user()->supplier_id)->SupplierName;
        } else {
            $supplierName = '';
        }

        $this->validate($request,[
            'EmployeeName' => 'required|string|max:191',
            // 'EmployeeCardname' => 'sometimes|string|min:6|unique:employees',
            // 'EmployeePhoto' => 'required'
        ]);
        if($request->EmployeePhoto){
            $name = time().'.'.explode('/', explode(':', substr($request->EmployeePhoto, 0, strpos($request->EmployeePhoto, ';')))[1])[1];

            \Image::make($request->EmployeePhoto)->save(public_path('img/profile/').$name);
            $request->merge(['EmployeePhoto' => $name]);
        } else{
            $request->merge(['EmployeePhoto' => 'employee.png']);
        }


        $newEmployee = Employee::create([
            'EmployeeName' => $request['EmployeeName'],
            'user_id' => auth()->id(),
            'EmployeeType' => $request['EmployeeType'],
            'EmployeeCardname' => $request['EmployeeCardname'],
            'EmployeePhoto' => $request['EmployeePhoto'],
        ]);

        if($request['EmployeeCardname']){
            $card = Card::where('CardName', $request['EmployeeCardname'])->first();
            if($card){                
                $card->employee_id = $newEmployee->id;

                History::create([
                    'CardName' => $request['EmployeeCardname'],
                    'EmployeeName' => $request['EmployeeName'],
                    'SupplierName' => $supplierName
                ]);
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

        if(auth()->user()->supplier_id){
            $supplierName = Supplier::find(auth()->user()->supplier_id)->SupplierName;
        } else {
            $supplierName = '';
        }

        $employee = Employee::find($id);

        $this->validate($request,[
            'EmployeeName' => 'required|string|max:191',
            'EmployeeType' => 'required',
        ]);

        $currentPhoto = $employee->EmployeePhoto;
        if($request->EmployeePhoto != $currentPhoto){
            $name = time().'.'.explode('/', explode(':', substr($request->EmployeePhoto, 0, strpos($request->EmployeePhoto, ';')))[1])[1];

            \Image::make($request->EmployeePhoto)->save(public_path('img/profile/').$name);
            $request->merge(['EmployeePhoto' => $name]);

            $employeePhoto = public_path('img/profile/').$currentPhoto;

            if(file_exists($employeePhoto) && $currentPhoto !='employee.png'){
                @unlink($employeePhoto);
            }
        }

        if($employee && ($employee->EmployeeCardname != $request->EmployeeCardname)){

            $oldCard = Card::where('CardName', $employee->EmployeeCardname)->first();
            if($oldCard){
               $oldCard->employee_id = null;
               $oldCard->save();
            }

            $newCard = Card::where('CardName', $request['EmployeeCardname'])->first();
            if($newCard){
                $newCard->employee_id = $employee->id;
                $newCard->save();

                History::create([
                    'CardName' => $request['EmployeeCardname'],
                    'EmployeeName' => $employee->EmployeeName,
                    'SupplierName' => $supplierName
                ]);
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
            $card->employee_id = null;
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

    public function getCardOptions(Request $request){

        $cards = Card::select();

        $supplierId = auth()->user()->supplier_id;

        if($supplierId){
            $supplier = Supplier::find($supplierId);
            if($supplier){
                $cardRange = $supplier->SupplierCardRange;

                $minCard = explode(',', $cardRange)[0];
                $maxCard = explode(',', $cardRange)[1];

                $cards = $cards->where('CardName', '>=', $minCard)->where('CardName', '<=', $maxCard);
            }
        }
        return $cards->whereNull('employee_id')->orderBy('created_at', 'desc')->get();
    }


    public function getCardOptionsEdit($id){

        $employee = Employee::findOrFail($id);

        $cards = Card::where('employee_id', null);

        $supplierId = auth()->user()->supplier_id;

        if($supplierId){
            $supplier = Supplier::find($supplierId);
            if($supplier){
                $cardRange = $supplier->SupplierCardRange;

                $minCard = explode(',', $cardRange)[0];
                $maxCard = explode(',', $cardRange)[1];

                $cards = $cards->where('CardName', '>=', $minCard)->where('CardName', '<=', $maxCard);
            }
        }

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
            });
        }else{
            $employees = $employees->orderBy('created_at', 'desc');
        }
        return $employees->with('card')->paginate(10);
    }

    public function getSuppliers()
    {
        return \App\Supplier::orderBy('created_at', 'desc')->get();
    }
}
