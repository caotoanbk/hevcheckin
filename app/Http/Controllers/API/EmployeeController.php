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

        if(auth()->user()->SupplierName){
            $employees = Employee::where('SupplierName', auth()->user()->SupplierName);
        } else {
            $employees = Employee::select();
        }

        if($type == 'allocated'){
            return $employees->whereNotNull('EmployeeCardname')->orderBy('created_at', 'desc')->paginate(10);
        }

        if($type == 'avaiable'){
            return $employees->whereNull('EmployeeCardname')->orderBy('created_at', 'desc')->paginate(10);
        }

        return $employees->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $supplierName = auth()->user()->SupplierName;

        $this->validate($request,[
            'EmployeeName' => 'required|string|max:191',
            'EmployeeCode' => 'required|string|unique:employees',
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
            'EmployeeCode' => $request['EmployeeCode'],
            'user_id' => auth()->id(),
            'EmployeeType' => $request['EmployeeType'],
            'EmployeeCardname' => $request['EmployeeCardname'],
            'EmployeePhoto' => $request['EmployeePhoto'],
            'SupplierName' => $supplierName
        ]);

        if($request['EmployeeCardname']){
            $card = Card::where('CardName', $request['EmployeeCardname'])->first();
            if($card){                
                $card->EmployeeCode = $newEmployee->EmployeeCode;

                History::create([
                    'CardName' => $request['EmployeeCardname'],
                    'EmployeeName' => $request['EmployeeName'],
                    'SupplierName' => $supplierName,
                    'EmployeeCode' => $request['EmployeeCode'],
                    'action' => 'Đang sử dụng'
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

        $supplierName = auth()->user()->SupplierName;

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
               $oldCard->EmployeeCode = null;
               $oldCard->save();
            }

            $newCard = Card::where('CardName', $request['EmployeeCardname'])->first();
            if($newCard){
                $newCard->EmployeeCode = $employee->EmployeeCode;

                History::create([
                    'CardName' => $request['EmployeeCardname'],
                    'EmployeeName' => $employee->EmployeeName,
                    'SupplierName' => $supplierName,
                    'EmployeeCode' => $employee->EmployeeCode,
                    'action' => 'Đang sử dụng'
                ]);
                $newCard->save();
            } else{
                History::create([
                    'CardName' => $employee->EmployeeCardname,
                    'EmployeeName' => $employee->EmployeeName,
                    'SupplierName' => $supplierName,
                    'EmployeeCode' => $employee->EmployeeCode,
                    'action' => 'Trả thẻ'
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
            if($card){                
                $card->EmployeeCode = null;
                $card->save();
            }
        }

        $employeePhoto = public_path('img/profile/').$employee->EmployeePhoto;

        if(file_exists($employeePhoto) && $employee->EmployeePhoto != 'employee.png'){
            @unlink($employeePhoto);
        }

        // delete the card

        $employee->delete();

        return ['message' => 'Employee Deleted'];
    }

    public function getCardOptions(Request $request){

        $cards = Card::select();

        $supplierName = auth()->user()->SupplierName;

        if($supplierName){
            $supplier = Supplier::find($supplierName);
            if($supplier){
                $cardRange = $supplier->SupplierCardRange;

                $minCard = explode(',', $cardRange)[0];
                $maxCard = explode(',', $cardRange)[1];

                $cards = $cards->where('CardName', '>=', 'Temporary worker '.$minCard)->where('CardName', '<=', 'Temporary worker '.$maxCard);
            }
        }
        return $cards->whereNull('EmployeeCode')->orderBy('created_at', 'desc')->get();
    }


    public function getCardOptionsEdit($id){

        $employee = Employee::findOrFail($id);

        $cards = Card::where('EmployeeCode', null);

        $supplierName = auth()->user()->SupplierName;

        if($supplierName){
            $supplier = Supplier::find($supplierName);
            if($supplier){
                $cardRange = $supplier->SupplierCardRange;

                $minCard = explode(',', $cardRange)[0];
                $maxCard = explode(',', $cardRange)[1];

                $cards = $cards->where('CardName', '>=','Temporary worker '.$minCard)->where('CardName', '<=', 'Temporary worker '.$maxCard);
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
                $query->orWhere('EmployeeCode', 'LIKE', "%$search%");
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
