<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Customer extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $s = $request->input('s');

        if (isset($s))
        {
            $customers = \App\Model\Customer::where('name', 'LIKE', $s)
                ->orderBy('id', 'DESC')
                ->paginate(10);

        } else {

            $customers = \App\Model\Customer::orderBy('id', 'DESC')
                ->paginate(10);
        }

        return view('customer.list', [
            'customers' => $customers,
            's' => $s
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('customer.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $customer = new \App\Model\Customer();

        $customer->name = $request->input('name');
        $customer->surname = $request->input('surname');
        $customer->address = $request->input('address');
        $customer->cod = Str::random(5);
        $customer->points = 0;

        $customer->save();

        return redirect()->route('customer');
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = \App\Model\Customer::find($id);

        return view('customer.form', [
            'customer' => $customer
        ]);
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
        $customer = \App\Model\Customer::find($id);

        $customer->name = $request->input('name');
        $customer->surname = $request->input('surname');
        $customer->address = $request->input('address');

        $customer->save();

        return redirect()->route('customer');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        \App\Model\Customer::destroy($id);

        return redirect()->route('customer');
    }
}