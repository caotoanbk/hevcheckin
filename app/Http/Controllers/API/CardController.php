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
        if($type == 'allocated'){
            return $cards->whereNotNull('employee_id')->orderBy('created_at', 'desc')->paginate(10);
        }

        if($type == 'avaiable'){
            return $cards->whereNull('employee_id')->orderBy('created_at', 'desc')->paginate(10);
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
        if(auth()->user()->supplier_id){
            $supplierName = Supplier::find(auth()->user()->supplier_id)->SupplierName;
        } else {
            $supplierName = '';
        }


        $this->validate($request,[
            'CardName' => 'required|string|max:191|unique:cards',
        ]);

        if($request['employee_id']){
            $employee = Employee::where('employee_id', $request['employee_id'])->first();
            if($employee){                
                $employee->EmployeeCardname = $request['CardName'];

                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeName' => $employee->EmployeeName,
                    'SupplierName' => $supplierName
                ]);
                $employee->save();
            }
        }

        return Card::create([
            'CardName' => $request['CardName'],
            'employee_id' => $request['employee_id']
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
        if(auth()->user()->supplier_id){
            $supplierName = Supplier::find(auth()->user()->supplier_id)->SupplierName;
        } else {
            $supplierName = '';
        }

        $card = Card::find($id);

        // $this->validate($request,[
        //     'CardName' => 'required|string|max:191|unique:cards,CardName,'.$card->id,
        // ]);

        if($card && ($card->employee_id != $request->employee_id)){

            $oldEmpl = Employee::where('id', $card->employee_id)->first();
            if($oldEmpl){
               $oldEmpl->EmployeeCardname = null;
               $oldEmpl->save();
            }

            $newEmpl = Employee::where('id', $request['employee_id'])->first();
            if($newEmpl){
                $newEmpl->EmployeeCardname = $card->CardName;
                $newEmpl->save();
                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeName' => $newEmpl->EmployeeName,
                    'SupplierName' => $supplierName
                ]);
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

        if($card->employee_id){
            $employee = Employee::findOrFail($card->employee_id);
            $employee->EmployeeCardname = null;
            $employee->save();
        }

        // delete the card

        $card->delete();

        return ['message' => 'Card Deleted'];
    }

    public function search() {
        $type = \Request::get('type') ? \Request::get('type') : '';

        if($type == 'allocated'){
            $cards = Card::whereNotNull('employee_id');
        } else if($type == 'avaiable'){
            $cards = Card::whereNull('employee_id');
        } else{
            $cards = Card::select();
        }

        if ($search = \Request::get('q')) {
            $cards = $cards->where(function($query) use ($search){
                $query->where('CardName', 'LIKE', "%$search%");
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
        return Employee::where('EmployeeCardName', null)->where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
    }

    public function getEmployeeOptionsEdit($id){

        $card = Card::findOrFail($id);

        $employees = Employee::where('EmployeeCardName', null);

        if($card->employee_id){
            $employees = $employees->orWhere('id', $card->employee_id);
        }

        return $employees->where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
    }
}
