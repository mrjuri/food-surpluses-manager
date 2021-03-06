<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Order extends Controller
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

        $orders = \App\Model\Order::with('customer')
            ->with('retail')
            ->where('reference', 'LIKE', '%' . $s . '%')
            ->orWhere('date', 'LIKE', '%' . $s . '%')
            ->orWhere('json_customer', 'LIKE', '%' . $s . '%')
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('orders.list', [
            'orders' => $orders,
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($args = array())
    {
        if (isset($args['id'])) {

            $order_id = $args['id'];

            DB::table('orders')
                ->where('id', $args['id'])
                ->update($args['data']);

        } else {

            $order_id = DB::table('orders')
                ->insertGetId($args['data']);
        }

        return $order_id;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = \App\Model\Order::with('user')
            ->with('retail')
            ->with('customer')
            ->find($id);

        $products = json_decode($order->json_products);

        return view('orders.show', [
            'order' => $order,
            'products' => $products
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        //
    }
}
