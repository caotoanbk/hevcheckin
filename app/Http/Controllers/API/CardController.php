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
        $supplierId = $request->get('supplierId');

        $cards = Card::select();

        if($supplierId != 'undefined' && $supplierId != 'null'){
            $supplier = Supplier::find($supplierId);
            if($supplier){
                $cardRange = $supplier->SupplierCardRange;

                $minCard = explode(',', $cardRange)[0];
                $maxCard = explode(',', $cardRange)[1];

                $cards = $cards->where('CardName', '>=', $minCard)->where('CardName', '<=', $maxCard);
            }
        }
        if($type == 'allocated'){
            return $cards->whereNotNull('EmployeeIdentity')->orderBy('created_at', 'desc')->with('employee')->paginate(15);
        }

        if($type == 'avaiable'){
            return $cards->whereNull('EmployeeIdentity')->orderBy('created_at', 'desc')->with('employee')->paginate(15);
        }

        return $cards->orderBy('created_at', 'desc')->with('employee')->paginate(15);
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
            'CardName' => 'required|string|max:191|unique:cards',
        ]);

        if($request['EmployeeIdentity']){
            $employee = Employee::where('EmployeeIdentity', $request['EmployeeIdentity'])->first();
            if($employee){                
                $employee->EmployeeCardname = $request['CardName'];
                $employee->save();

                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeIdentity' => $request['EmployeeIdentity']
                ]);
            }
        }

        return Card::create([
            'CardName' => $request['CardName'],
            'EmployeeIdentity' => $request['EmployeeIdentity']
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
        $card = Card::find($id);

        // $this->validate($request,[
        //     'CardName' => 'required|string|max:191|unique:cards,CardName,'.$card->id,
        // ]);

        if($card && ($card->EmployeeIdentity != $request->EmployeeIdentity)){

            $oldEmpl = Employee::where('EmployeeIdentity', $card->EmployeeIdentity)->first();
            if($oldEmpl){
               $oldEmpl->EmployeeCardname = null;
               $oldEmpl->save();
            }

            $newEmpl = Employee::where('EmployeeIdentity', $request['EmployeeIdentity'])->first();
            if($newEmpl){
                $newEmpl->EmployeeCardname = $card->CardName;
                $newEmpl->save();
                History::create([
                    'CardName' => $request['CardName'],
                    'EmployeeIdentity' => $request['EmployeeIdentity']
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

        if($card->EmployeeIdentity){
            $employee = Employee::findOrFail($card->EmployeeIdentity);
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
            $cards = Card::whereNotNull('EmployeeIdentity');
        } else if($type == 'avaiable'){
            $cards = Card::whereNull('EmployeeIdentity');
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
        return $cards->with('employee')->paginate(15);
    }

    public function getEmployeeOptions(){
        return Employee::where('EmployeeCardName', null)->orderBy('created_at', 'desc')->get();
    }

    public function getEmployeeOptionsEdit($id){

        $card = Card::findOrFail($id);

        $employees = Employee::where('EmployeeCardName', null);

        if($card->EmployeeIdentity){
            $employees = $employees->orWhere('EmployeeIdentity', $card->EmployeeIdentity);
        }

        return $employees->orderBy('created_at', 'desc')->get();
    }
}
