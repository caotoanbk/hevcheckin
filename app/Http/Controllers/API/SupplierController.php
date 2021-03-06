<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Supplier::orderBy('created_at', 'desc')->paginate(15);
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
            'SupplierName' => 'required|string|max:191',
            // 'SupplierInfo' => 'required|string',
            'SupplierCardRange' => 'required|string|max:20',
        ]);
        return Supplier::create([
            'SupplierName' => $request['SupplierName'],
            'SupplierInfo' => $request['SupplierInfo'],
            'SupplierCardRange' => $request['SupplierCardRange'],
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
        $supplier = Supplier::find($id);

        $this->validate($request,[
            'SupplierName' => 'required|string|max:191',
            // 'SupplierInfo' => 'required|string',
            'SupplierCardRange' => 'required|string|max:20',
        ]);

        $supplier->update($request->all());
        return ['message' => 'Updated the supplier info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // delete the supplier

        $supplier->delete();

        return ['message' => 'Supplier Deleted'];
    }
}
