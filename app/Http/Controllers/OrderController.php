<?php

namespace App\Http\Controllers;

use App\Order;
use App\Produk;
use App\Customer;
use App\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::where('status', null)->orderBy('id', 'desc')->get();
        return view('admin.order.index', ['orders' => $orders]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::with('data_order_detail', 'data_order_detail.data_produk')
        ->find($id);

        return view('admin.order.detail', ['order' => $order]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::find($id);
        return view('admin.order.edit', ['order' => $order]);
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
        $order = Order::find($id);
        $order->status_bayar = $request->status_bayar;
        $order->save();

        if ($request->total_bayar > 299) {
            $total_bayar = $request->total_bayar;
            $hitung_point = floor($total_bayar / 300);

            $customer = Customer::find($order->customer_id);
            $customer->poin = $customer->poin + $hitung_point;
            $customer->save();
        }        

        $request->session()->flash('status', 'Data berhasil diubah');
        
        return redirect()->route('order.edit', ['id' => $id]);
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

    public function hapus(Request $request, $id)
    {
        $order = Order::find($id);
        
        $order->delete();

        $destinationPath = public_path('/img');
        File::delete($destinationPath . '/' . $order->gambar1);

        $request->session()->flash('status', 'Data berhasil dihapus');
        
        return redirect()->route('order.index');
    }

    public function selesai(Request $request, $id)
    {
        $order = Order::find($id);
        $order->status = 1;
        $order->save();

        $order_detail = OrderDetail::where('kode', $order->kode)->first();

        $qty = $order_detail->qty;

        $produk = Produk::find($order_detail->produk_id);
        $produk->terjual = $produk->terjual + $qty;
        $produk->save();

        $request->session()->flash('status', 'Data bisa di lihat di menu history');

        return redirect()->route('order.index');
    }

    public function history()
    {
        $orders = Order::where('status', 1)
        ->orderBy('id', 'desc')
        ->get();

        return view('admin.order.history', ['orders' => $orders]);
    }

    public function historyDetail($id)
    {
        $order = Order::with('data_order_detail', 'data_order_detail.data_produk')
        ->find($id);

        return view('admin.order.historyDetail', ['order' => $order]);
    }

    public function historyHapus(Request $request, $id)
    {
        $order = Order::find($id);
        
        $order->delete();

        $destinationPath = public_path('/img');
        File::delete($destinationPath . '/' . $order->gambar1);

        $request->session()->flash('status', 'Data berhasil dihapus');
        
        return redirect()->route('order.history');
    }
}
