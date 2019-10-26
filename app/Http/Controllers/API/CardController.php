<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Card;
use App\Employee;
use Illuminate\Support\Facades\Hash;

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
        if($type == 'allocated'){
            return Card::whereNotNull('employee_id')->orderBy('created_at', 'desc')->with('employee')->paginate(15);
        }

        if($type == 'avaiable'){
            return Card::whereNull('employee_id')->orderBy('created_at', 'desc')->with('employee')->paginate(15);
        }

        return Card::orderBy('created_at', 'desc')->with('employee')->paginate(15);
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

        if($request['employee_id']){
            $employee = Employee::findOrFail($request['employee_id']);
            if($employee){                
                $employee->EmployeeCardname = $request['CardName'];
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
        $card = Card::find($id);

        // $this->validate($request,[
        //     'CardName' => 'required|string|max:191|unique:cards,CardName,'.$card->id,
        // ]);

        if($card && ($card->employee_id != $request->employee_id)){

            $oldEmpl = Employee::find($card->employee_id);
            if($oldEmpl){
               $oldEmpl->EmployeeCardname = null;
               $oldEmpl->save();
            }

            $newEmpl = Employee::find($request['employee_id']);
            if($newEmpl){
                $newEmpl->EmployeeCardname = $card->CardName;
                $newEmpl->save();
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
            $cards = Card::where(true);
        }

        if ($search = \Request::get('q')) {
            $cards = $cards->where(function($query) use ($search){
                $query->where('CardName', 'LIKE', "%$search%");
                $query->where('CardName', 'LIKE', "%$search%");
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

        if($card->employee_id){
            $employees = $employees->orWhere('id', $card->employee_id);
        }

        return $employees->orderBy('created_at', 'desc')->get();
    }
}
