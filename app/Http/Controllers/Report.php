<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Report extends Controller
{
    var $path_report_csv = 'report/csv/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get_reports($date_search = '', $type = 'products')
    {
        if ($date_search == '') {

            $date_search = date('Y-m-d');

        } else {

            $date_search = date('Y-m-d', strtotime(str_replace('/', '-', $date_search)));
        }

        $orders = \App\Model\Order::where('date', 'LIKE', $date_search . '%')
            ->get();
        $reports = array();

        // Prendo la lista degli ordini
        foreach ($orders as $order) {

            $products_obj = json_decode($order->json_products);
            $customer_obj = json_decode($order->json_customer);

            // Report clienti
            if ($type == 'products') {

                // Ogni ordine ha una lista di prodotti che ciclo
                foreach ($products_obj as $product) {

                    // Recupero soltanto i prodotti FEAD
                    if ($product->type == 'fead') {

                        if (!isset($n_family_total[$customer_obj->cod]))
                            $n_family_total[$product->cod][$customer_obj->cod] = 0;

                        if (!isset($reports[$product->cod]['kg']))
                            $reports[$product->cod]['kg'] = 0;

                        if (!isset($reports[$product->cod]['amount']))
                            $reports[$product->cod]['amount'] = 0;

                        $reports[$product->cod]['product'] = $product;
                        $reports[$product->cod]['kg'] += $product->kg;
                        $reports[$product->cod]['amount'] += $product->amount;

                        // Se voglio contare tutte le volte che ogni cliente ha acquistato il prodotto
                        /*$reports[$product->cod]['customers'][] = $customer_obj;
                        $n_family_total[$product->cod][] = $customer_obj->family_number;*/

                        // Se voglio contare da quale singola famiglia ?? stato acquistato il prodotto
                        $reports[$product->cod]['customers'][$customer_obj->cod] = $customer_obj;
                        $n_family_total[$product->cod][$customer_obj->cod] = $customer_obj->family_number;

                        $reports[$product->cod]['customers_count'] = array(
                            'n_family' => count($reports[$product->cod]['customers']),
                            'n_family_total' => array_sum($n_family_total[$product->cod])
                        );

                    }
                }

            }

            // Report clienti
            if ($type == 'customers') {

                $fead = 0;

                foreach ($products_obj as $product) {

                    if ($product->type == 'fead') {

                        $fead = 1;
                        break;

                    }

                }

                if ($fead == 1) {
                    $reports[$customer_obj->id] = $customer_obj;
                }

            }
        }

        if ($type == 'customers') {

            $report_family['family'] = count($reports);
            $report_family['family_number'] = 0;

            foreach ($reports as $report) {

                $report_family['family_number'] += $report->family_number;

            }

            $reports = $report_family;
        }

        return $reports;
    }

    public function index(Request $request)
    {
        $s = $request->input('s');

        $reports_customers = $this->get_reports($s, 'customers');
        $reports_products = $this->get_reports($s, 'products');

        return view('report.list', [
            'reports_customers' => $reports_customers,
            'reports_procuts' => $reports_products,
            's' => $s
        ]);
    }

    public function csvMake($type, $data, $out_name)
    {
        if (
            (count($data) > 0 && $type == 'products') ||
            (isset($data['family']) && $data['family'] > 0 && $type == 'customers')
        ) {

            // Creo la directory
            $path_reportCSV_send = Storage::disk('public')->makeDirectory($this->path_report_csv . 'send');
            $path_reportCSV_queue = Storage::disk('public')->makeDirectory($this->path_report_csv . 'queue');

            if ($path_reportCSV_queue) {

                if ($type == 'customers') {

                    $csv_content = 'Famiglie;Componenti' . "\n";

                    $csv_content .= $data['family'] . ';';
                    $csv_content .= $data['family_number'];
                    $csv_content .= "\n";

                }

                if ($type == 'products') {

                    $csv_content = 'Prodotto;kg.;q.t??' . "\n";

                    foreach ($data as $d) {
                        $csv_content .= $d['product']->cod . ' - ' . $d['product']->name . ';';
                        $csv_content .= $d['kg'] . ';';
                        $csv_content .= $d['amount'];
                        $csv_content .= "\n";
                    }

                }

                $path_report_csv = Storage::disk('public')->put(
                    $this->path_report_csv . 'queue/' . $out_name,
                    $csv_content
                );
            }
        }
    }

    public function mailSendWeb(Request $request)
    {
        $s = $request->input('s');

        $this->mailSend($s);

        return redirect()->route('report');
    }

    public function mailSend($date_send = '')
    {
        if ($date_send == '') {

            $date_send = date('d/m/Y');
            $name_file = date('Ymd');

        } else {

            $date_time = strtotime(str_replace('/', '-', $date_send));
            $date_send = date('d/m/Y', $date_time);
            $name_file = date('Ymd', $date_time);
        }

//        $host = current(explode('.', \request()->getHttpHost()));
        $host = env('APP_DOMAIN');

        // Creo i file CSV
        $this->csvMake('products', $this->get_reports($date_send, 'products'), $host . '_prodotti_' . $name_file . '.csv');
        $this->csvMake('customers', $this->get_reports($date_send, 'customers'), $host . '_famiglie_' . $name_file . '.csv');
        $files = Storage::disk('public')->files($this->path_report_csv . 'queue/');

        // Verifico se esistono file da inviare
        if (count($files) > 0) {

            // Invio email con i file CSV come allegato
            Mail::to(env('MAIL_TO'))
                ->send(new \App\Mail\Report(array(
                    'host' => $host,
                    'attach_path' => $this->path_report_csv . 'queue/'
                )));

            // Se la mail ?? andata a buon fine sposto i file CSV nella directory send
            if (count(Mail::failures()) == 0) {

                $files = Storage::disk('public')->files($this->path_report_csv . 'queue/');

                foreach ($files as $file) {

                    // Se il file esiste viene eliminato
                    Storage::disk('public')->delete($this->path_report_csv . 'send/' . basename($file));

                    Storage::disk('public')->move(
                        $file,
                        $this->path_report_csv . 'send/' . basename($file)
                    );
                }
            }
        }
    }

}
