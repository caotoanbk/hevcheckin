<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Card;
use App\Employee;
use Illuminate\Support\Facades\Hash;
use App\History;
use App\Supplier;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->get('type');
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
        if($type == 'allocated'){
            return $cards->whereNotNull('EmployeeCode')->orderBy('EmployeeCode', 'desc')->paginate(10);
        }

        if($type == 'avaiable'){
            return $cards->whereNull('EmployeeCode')->orderBy('created_at', 'desc')->paginate(10);
        }

        return $cards->orderBy('created_at', 'desc')->paginate(10);
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
            'CardName' => 'required|string|max:191|unique:cards',
        ]);

        if($request['EmployeeCode']){
            $employee = Employee::where('EmployeeCode', $request['EmployeeCode'])->first();
            if($employee){                
                $employee->EmployeeCardname = $request['CardName'];

                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeName' => $employee->EmployeeName,
                    'SupplierName' => $supplierName,
                    'EmployeeCode' => $request['Employeecode'],
                    'action' => 'Đang sử dụng'
                ]);
                $employee->save();
            }
        }

        return Card::create([
            'CardName' => $request['CardName'],
            'EmployeeCode' => $request['EmployeeCode']
        ]);
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

        $card = Card::find($id);

        // $this->validate($request,[
        //     'CardName' => 'required|string|max:191|unique:cards,CardName,'.$card->id,
        // ]);

        if($card && ($card->EmployeeCode != $request->EmployeeCode)){

            $oldEmpl = Employee::where('EmployeeCode', $card->EmployeeCode)->first();
            if($oldEmpl){
               $oldEmpl->EmployeeCardname = null;
               $oldEmpl->save();
            }

            $newEmpl = Employee::where('EmployeeCode', $request['EmployeeCode'])->first();
            if($newEmpl){
                $newEmpl->EmployeeCardname = $card->CardName;
                $newEmpl->save();
                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeName' => $newEmpl->EmployeeName,
                    'SupplierName' => $supplierName,
                    'action' => 'Đang sử dụng',
                    'EmployeeCode' => $newEmpl->EmployeeCode
                ]);
            }else{
                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeName' => Employee::find($card->EmployeeCode)->EmployeeName,
                    'SupplierName' => $supplierName,
                    'EmployeeCode' => $card->EmployeeCode,
                    'action' => 'Trả thẻ'
                ]);
                // $card->EmployeeCode = null;
                // $card->save();
            }

        }

        $card->update($request->all());
        return ['message' => 'Updated the card info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $card = Card::findOrFail($id);

        if($card->EmployeeCode){
            $employee = Employee::find($card->EmployeeCode);
            if($employee){            
                $employee->EmployeeCardname = null;
                $employee->save();
            }
        }

        // delete the card

        $card->delete();

        return ['message' => 'Card Deleted'];
    }

    public function search() {
        $type = \Request::get('type') ? \Request::get('type') : '';

        if($type == 'allocated'){
            $cards = Card::whereNotNull('EmployeeCode');
        } else if($type == 'avaiable'){
            $cards = Card::whereNull('EmployeeCode');
        } else{
            $cards = Card::select();
        }

        if ($search = \Request::get('q')) {
            $cards = $cards->where(function($query) use ($search){
                $query->where('CardName', 'LIKE', "%$search%");
                $query->orWhere('EmployeeCode', 'LIKE', "%$search%");
                // $query->where('CardName', 'LIKE', "%$search%");
            });
        }else{
            $cards = $cards->orderBy('created_at', 'desc');
        }

        $supplier = Supplier::find(\Auth::user()->supplier_id);

        if($supplier){
            $cardRange = $supplier->SupplierCardRange;

            $minCard = explode(',', $cardRange)[0];
            $maxCard = explode(',', $cardRange)[1];

            $cards = $cards->where('CardName', '>=', $minCard)->where('CardName', '<=', $maxCard);
        }

        return $cards->with('employee')->paginate(10);
    }

    public function getEmployeeOptions(){
        return Employee::where('EmployeeCardname', null)->where('SupplierName', 'like', '%'.auth()->user()->SupplierName.'%')->orderBy('created_at', 'desc')->get();
    }

    public function getEmployeeOptionsEdit($id){

        $card = Card::findOrFail($id);

        $employees = Employee::where('EmployeeCardName', null);

        if($card->EmployeeCode){
            $employees = $employees->orWhere('EmployeeCode', $card->EmployeeCode);
        }

        return $employees->where('SupplierName', 'like','%'.auth()->user()->SupplierName.'%')->orderBy('created_at', 'desc')->get();
    }
}
