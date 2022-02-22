<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expedisi;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use App\Models\InvoicePayment;
use App\Models\InvoicePiutang;
use App\Models\InvoiceTandaTerima;
use App\Exports\TandaTerimaExports;
use App\Models\TransactionSalesFee;

class PembayaranController extends Controller
{
    public function index()
    {
        $companies = Perusahaan::orderBy('name', 'asc')->get();
        $members = Member::orderBy('name', 'asc')->get();
        $invoice_tanda_terima = InvoiceTandaTerima::with('invoiceNoNota')->orderBy('created_at', 'desc')->groupBy('no_tanda_terima')->get();
        $dataSearch = null;

        return view ('backend.tandaterima.pembayaran.index', compact('companies', 'members', 'invoice_tanda_terima', 'dataSearch'));
    }

    public function search(Request $request)
    {
        $companies = Perusahaan::orderBy('name', 'asc')->get();
        $members = Member::orderBy('name', 'asc')->get();
        $invoiceNumber = $request->invoice;

        if ($request->invoice == null && $request->tanda_terima == null && $request->customer == null && $request->perusahaan == null) {
            $invoice_tanda_terima = InvoiceTandaTerima::with('invoiceNoNota')->orderBy('created_at', 'desc')->groupBy('no_tanda_terima')->get();
        } else {
            $invoice_tanda_terima = InvoiceTandaTerima::when(request()->tanda_terima, function($query) {
                                                        $query->where('no_tanda_terima', request()->tanda_terima)->distinct();
                                                    })
                                                    ->when(request()->perusahaan, function($query) {
                                                        $query->where('perusahaan_id', request()->perusahaan)->distinct();
                                                    })
                                                    ->when(request()->customer, function($query) {
                                                        $query->where('member_id', request()->customer)->distinct();
                                                    })
                                                    ->whereHas('getInvoice', function($q) use ($invoiceNumber){
                                                        $q->where('no_nota', 'LIKE', '%'.$invoiceNumber.'%');
                                                    })
                                                    ->select('id','no_tanda_terima', 'invoice_id', 'member_id', 'perusahaan_id', 'created_at')
                                                    ->groupBy('no_tanda_terima')
                                                    ->get();
        }
        // dd($invoice_tanda_terima);
        // return response()->json([
        //     'data' => $invoice_tanda_terima
        // ]);

        // return view ('backend.tandaterima.pembayaran.result_search', compact('invoice_tanda_terima'));
        $dataSearch = $request->all();

        return view ('backend.tandaterima.pembayaran.index', compact('companies', 'members', 'invoice_tanda_terima', 'dataSearch'));
    }

    public function detail($id)
    {
        $ids = \Crypt::decrypt($id);

        $invoice_tanda_terima = InvoiceTandaTerima::where('id', $ids)->first();
        $payments = Payment::orderBy('name', 'desc')->get();

        return view ('backend.tandaterima.pembayaran.detail', compact('invoice_tanda_terima', 'payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_pembayaran' => 'required',
            'date' => 'required',
            'nilai' => 'required',
        ]);

        $no_tt = $request->no_tanda_terima;

        //get data invoice where pay status 0 (belum lunas) order by invoice date created_at asc
        $invoice_tanda_terima = InvoiceTandaTerima::whereHas('getInvoice', function($q){
                                                        $q->where('pay_status', 0);
                                                    })
                                                    ->where('no_tanda_terima', $no_tt)->orderBy('invoice_date', 'asc')->get();
        // dd($no_tt);

        $cek = InvoicePayment::where('no_tanda_terima', $no_tt)
                                ->orderBy('created_at', 'desc')
                                ->first();

        $sum_total_sudah_dibayar = InvoicePayment::where('no_tanda_terima', $no_tt)
                                ->sum('sudah_dibayar');

        if ($cek) {
            $hitung = $cek->cicilan_ke + 1;
        } else {
            $hitung = 1;
        }

        if($request->jenis_pembayaran == "1" || $request->jenis_pembayaran == "4"){
            $tanggal_fil = date("Y-m-d",strtotime("+0 day", strtotime($request->tanggal_cair)));
        }else{
            $tanggal_fil = date("Y-m-d",strtotime("+0 day", strtotime($request->date)));
        }

        $total = $invoice_tanda_terima->sum('nilai')-($sum_total_sudah_dibayar+str_replace('.','',$request->nilai));
        // dd($total);
        $insert_invoice_payment = InvoicePayment::create([
            'no_tanda_terima'   => $no_tt,
            // 'member_id'         => $invoice_tanda_terima->member_id,
            'payment_id'        => $request->jenis_pembayaran,
            'keterangan'        => $request->keterangan ? $request->keterangan : null,
            'payment_date'      => date("Y-m-d",strtotime("+0 day", strtotime($request->date))),
            'liquid_date'       => $request->tanggal_cair ? date("Y-m-d",strtotime("+0 day", strtotime($request->tanggal_cair))) : null,
            'name'              => $request->nama_giro_cek ? $request->nama_giro_cek : null,
            'number'            => $request->no_giro_cek ? $request->no_giro_cek : null,
            'sudah_dibayar'     => str_replace('.','',$request->nilai),
            'sisa'              => $total,
            'total_pembayaran'  => $invoice_tanda_terima->sum('nilai'),
            'cicilan_ke'        => $hitung,
            'filter_date'       => $tanggal_fil,
            'flag_giro_cek'     => $request->jenis_pembayaran == 1 || $request->jenis_pembayaran == 4 ? '1' : '0',
        ]);


        if ($total == 0 || $total <= 6000) {

            InvoicePiutang::create([
                // 'invoice_id'        => $invoice_tanda_terima->invoice_id,
                'no_tt'             => $no_tt,
                'invoice_payment_id'=> $insert_invoice_payment->id,
                'total'             => $cek ? $cek->total_pembayaran : $invoice_tanda_terima->sum('nilai'),
                'sisa'              => $cek ? $cek->sisa-str_replace('.','',$request->nilai) : $total,
                'payment_id'        => $request->jenis_pembayaran,
                'tanggal'           => date("Y-m-d",strtotime("+0 day", strtotime($request->date))),
                'flag'              => 1,
            ]);

            foreach ($invoice_tanda_terima as $inv_tt) {

                //batas waktu mendapatkan fee sales
                $duedate_fee_sales = date("Y-m-d H:i",strtotime("+120 day", strtotime($inv_tt->invoice_date)));

                $fee = $inv_tt->getInvoice->total * 0.5 / 100;
                if($duedate_fee_sales >= date('Y-m-d H:i')){
                    TransactionSalesFee::create([
                        'invoice_id' => $inv_tt->invoice_id,
                        'sales_id' => $inv_tt->getInvoice->sales_id,
                        'fee' => $fee,
                    ]);
                }

                $invoice = Invoice::where('id', $inv_tt->invoice_id)
                                ->where('pay_status', 0)
                                ->update([
                                    'pay_status' => 1
                                ]);

            }

            InvoicePayment::where('no_tanda_terima',$no_tt)->update([
                                                                'flag' => 1
                                                            ]);
        } else {
            $insert_invoice_piutang = InvoicePiutang::create([
                // 'invoice_id'        => $invoice_tanda_terima->invoice_id,
                'no_tt'             => $no_tt,
                'invoice_payment_id'=> $insert_invoice_payment->id,
                'total'             => $invoice_tanda_terima->sum('nilai'),
                'sisa'              => $total,
                'payment_id'        => $request->jenis_pembayaran,
                'tanggal'           => date("Y-m-d",strtotime("+0 day", strtotime($request->date))),
                'flag'              => 0,
            ]);
        }

        return redirect()->back()->with('status', 'Pembayaran berhasil dilakukan');
    }

    public function delete($id)
    {
        $inv_payment = InvoicePayment::find($id);
        $no_tanda_terima = $inv_payment->no_tanda_terima;
        $invoice_tanda_terima = InvoiceTandaTerima::where('no_tanda_terima', $no_tanda_terima)->get();
        $sum_total_sudah_dibayar = InvoicePayment::where('no_tanda_terima', $no_tanda_terima)
                                ->sum('sudah_dibayar');
        $total          = $invoice_tanda_terima->sum('nilai') - $sum_total_sudah_dibayar;

        if ($total > 6000) {
            foreach ($invoice_tanda_terima as $key => $inv_tt) {

                Invoice::where('id', $inv_tt->invoice_id)
                                ->where('pay_status', 1)
                                ->update([
                                    'pay_status' => 0
                                ]);
                TransactionSalesFee::where('invoice_id', $inv_tt->invoice_id)->delete();

            }

            InvoicePayment::where('no_tanda_terima',$no_tanda_terima)->update([
                                                                'flag' => 0
                                                            ]);
        }

        $inv_payment = InvoicePayment::find($id)->delete();
        $inv_piutang = InvoicePiutang::where('invoice_payment_id', $id)->delete();

        return redirect()->back()->with('status', 'History Pembayaran berhasil dihapus');
    }

    public function menu_data_list(Request $request)
    {
        $list = '';
        $list.='<a href="#!" class="btn bg-warning" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="print"><i class="fa fa-print"></i>&nbsp; Print</a>&nbsp;';
        $list.='<a href="#!" class="btn bg-success" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="excel"><i class="fa fa-file-excel-o"></i>&nbsp; Excel</a>&nbsp;';
        $list.='<a href="#!" class="btn bg-danger" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="pdf"><i class="fa fa-file-pdf-o"></i>&nbsp; PDF</a>';

        return response()->json([
            'list' => $list
        ]);
    }

    public function data_pembayaran($menu, $idtt){
        $dt = new Carbon();
        $tanggal = $dt->now()->isoFormat('D MMM Y');
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.*','perusahaan.id as perusahan_id','perusahaan.name as perusahaan_name','perusahaan.rek_no as perushaan_rekno','member.id as member_id','member.name as member_name','member.address_toko as member_toko','member.city as member_kota');
        $query->join('perusahaan','invoice_tanda_terima.perusahaan_id','perusahaan.id');
        $query->join('member','invoice_tanda_terima.member_id','member.id');
        $query->where('invoice_tanda_terima.id', $idtt);
        $tanda_terima = $query->first();
        $tanda_terima->print_tanggal = $tanggal;
        $grandtotal = InvoiceTandaTerima::where('no_tanda_terima', $tanda_terima->no_tanda_terima)->sum('nilai');
        $tanda_terima->grandtotal = $grandtotal;

        $querydetail = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as no_nota', 'invoice.id as invoid','invoice.dateorder as dateorder');
        $querydetail->join('invoice','invoice_tanda_terima.invoice_id','invoice.id');
        $querydetail->where('invoice_tanda_terima.no_tanda_terima', $tanda_terima->no_tanda_terima);

        $detail_tanda_terima = $querydetail->get();

        foreach ($detail_tanda_terima as $key => $value) {
            $value->no = $key+1;
            $value->pertanggal = date('d M y', strtotime(date($value->dateorder)));
        }

        $title = 'Tanda Terima '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

        if($menu == 'pdf'){
            $config = [
                'mode'                  => '',
                'format'                => 'A4',
                'default_font_size'     => '12',
                'default_font'          => 'sans-serif',
                'margin_left'           => 15,
                'margin_right'          => 15,
                'margin_top'            => 5,
                'margin_bottom'         => 0,
                'margin_header'         => 0,
                'margin_footer'         => 0,
                'orientation'           => 'L',
                'title'                 => 'CETAK INVOICE',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
            $pdf = \PDF::loadView('backend.tandaterima.print_tanda_terima.pdf',['title' => $title,'tanda_terima' => $tanda_terima,'detail_tanda_terima' => $detail_tanda_terima,'tanggal' => $tanggal ],[],$config);
            ob_get_clean();
            return $pdf->stream('Tanda Terima "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            return view('backend/tandaterima/print_tanda_terima/print', compact('tanda_terima','detail_tanda_terima','tanggal','title'));
        }else if($menu == 'excel'){
            return \Excel::download(new TandaTerimaExports($idtt),'Tanda_Terima_'.$tanggal.'.xlsx');
        }
    }

    public function input_pengiriman(Request $request){
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.expedisi','invoice_tanda_terima.id','invoice_tanda_terima.no_tanda_terima','invoice_tanda_terima.delivery_date','invoice_tanda_terima.resi_no','invoice_tanda_terima.nilai','invoice_tanda_terima.nilai_pengiriman','invoice.no_nota as no_nota','invoice.total as total_invoice','invoice.sales_id as sales_id','invoice.duedate as invoice_duedate','invoice.min_duedate as invoice_min_duedate', 'invoice.expedisi as invoice_expedisi');
        $query->join('invoice','invoice.id', 'invoice_tanda_terima.invoice_id');
        $query->where('invoice_tanda_terima.no_tanda_terima', $request->enc_id);
        $invoice = $query->get();
        //dd($invoice);
        $total = $query->count();

        $expedisi = Expedisi::all();

        $content = '';
        $js = '';
        foreach($invoice as $key => $value){
            if($value->delivery_date == "" || $value->delivery_date==null ){
                $date_kirim = "".date("Y-m-d",strtotime("-1 day", strtotime(date("Y-m-d"))))."";
            }else{
                $date_kirim = "".date("Y-m-d",strtotime("+0 day", strtotime($value->delivery_date)))."";
            }

            // $selectedExpedisi = $value->expedisi;
            $selectedExpedisi = Expedisi::find($value->invoice_expedisi);
            //dd($selectedExpedisi);
            $content.='<div class="panel-heading text-dark">';
                $content.='<h5 class="panel-title">Invoice No : <b>'.$value->no_nota.' : </b></h5>';
            $content.='</div>';
            $content.='<div class="panel-body">';
                $content.='<div class="row">';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>Tanggal Kirim :</label>';
                            $content.='<input type="date" id="delivery_date_'.$value->id.'" name="delivery_date_'.$value->id.'" class="form-control" data-mask="99/99/9999" placeholder="Masukkan Tanggal Kirim" value="'.$date_kirim.'">';
                        $content.='</div>';
                    $content.='</div>';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>Expedisi :</label>';
                            $content.= '<select class="exspedisi_'.$value->id.' form-control" id="exspedisi_'.$value->id.'" name="exspedisi_'.$value->id.'">';
                            $content.='<option value="'.$selectedExpedisi->name.'">'.$selectedExpedisi->name.'</option>';
                            foreach($expedisi as $key => $result){
                                if($result->id == $value->invoice_expedisi){
                                    $selecteddetail = 'selected';
                                }else{
                                    $selecteddetail = '';
                                }

                                $content.='<option value="'.$result->id.'" '.$selecteddetail.'>'.ucfirst($result->name).'</option>';
                            }
                            $content.='</select>';
                        $content.='</div>';
                    $content.='</div>';
                $content.='</div>';
                $content.='<div class="row">';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>No Resi :</label>';
                            $content.='<input type="text" id=resi_no_'.$value->id.'" name="resi_no_'.$value->id.'" class="form-control" placeholder="No Resi" value="'.$value->resi_no.'">';
                        $content.='</div>';
                    $content.='</div>';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>Nilai (Rp.) :</label>';
                            $content.='<input type="text" id=nilai_'.$value->id.' class="form-control numberformat" value='.number_format($value->nilai_pengiriman, 0,'.','.').' name=nilai_'.$value->id.'>';
                        $content.='</div>';
                    $content.='</div>';
                $content.='</div>';
            $content.='</div>';

            // $js.="$('.exspedisi_".$value->id."').select2({
            //     dropdownParent: $('#modal_pengiriman')
            // })";

        }
        return response()->json([
            'html' => $content,
            'js' => $js
        ]);
    }

    public function simpan_pengiriman(Request $request){
        $tanda_terima = InvoiceTandaTerima::where('no_tanda_terima', $request->id_tanter)->get();

        try {
            foreach ($tanda_terima as $key => $value) {
                $update_tanter  = InvoiceTandaTerima::find($value->id)->update([
                    'resi_no'          => $request->input('resi_no_'.$value->id),
                    'expedisi'         => $request->input('exspedisi_'.$value->id),
                    'delivery_date'    => date("Y-m-d",strtotime("+0 day", strtotime($request->input('delivery_date_'.$value->id)))),
                    'nilai_pengiriman' => str_replace('.','',$request->input('nilai_'.$value->id)),
                ]);
                $invoice = InvoiceTandaTerima::find($value->id)->getInvoice->update([
                    'resi_no'       => $request->input('resi_no_'.$value->id),
                    'expedisi'      => $request->input('exspedisi_'.$value->id),
                    'delivery_date' => date("Y-m-d",strtotime("+0 day", strtotime($request->input('delivery_date_'.$value->id)))),
                ]);
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Data Berhasil Diupdate'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Data Tidak Ada'
            ]);
        }
    }
}
