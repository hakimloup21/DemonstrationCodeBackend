<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Site;
use DB;
use App\BonSortieMag;
use App\BonSortieMagDet;
use App\destination;
use App\stock;
use PDF;
use App\Events\addBon_S;
use App\Events\addBonS_up;
use Carbon\Carbon;
use Excel;
use Illuminate\Support\Collection;
use App\Exports\BonSortieMagExport;
use DataTables;
use Form;
use File;
use Response;
use Image;
use Storage;
use App\Imports\BonSortieImport;
use Auth;
use App\BonHorsLigne;


class BonSortieMagController extends Controller
{
    public function getdataEditBonSM(Request $request)
    {
        $tab = $request->get('tab');
        $id = $request->get('id');

        if ($tab) {

            $idst = DB::table('bon_sortie_mags')
                ->select()
                ->where('bon_sortie_mags.id', $id)
                ->get();

            $produits = DB::table('stocks')
                ->join('products', 'stocks.numprod', '=', 'products.id')
                ->select('products.id', 'products.designation', 'products.codebarre', 'stocks.qteAchat', 'stocks.prixAchat')
                ->where('stocks.id', $idst[0]->idStock)
                ->whereNotIn('stocks.numprod', $tab);
        } else {
            $idst = DB::table('bon_sortie_mags')
                ->select()
                ->where('bon_sortie_mags.id', $id)
                ->get();

            $produits = DB::table('stocks')
                ->join('products', 'stocks.numprod', '=', 'products.id')
                ->select('products.id', 'products.designation', 'products.codebarre', 'stocks.qteAchat', 'stocks.prixAchat')
                ->where('stocks.id', $idst[0]->idStock)
                ->whereNotIn('stocks.numprod', function ($query) use ($id, $idst) {
                    $query->select('numprod')->from('bon_sortie_mag_dets')
                        ->where('bon_sortie_mag_dets.idbon', $id);
                });
        }

        return Datatables::of($produits)
            ->addColumn('cocher', function ($produits) {
                return '<div class="checkbox-radios"> <div class="form-check"> <label class="form-check-label"> <input type="checkbox" class="form-check-input" name="produitid2[]" value="{{' . $produits->id . '}}"> <span class="form-check-sign"><span class="check"></span></span></label></div></div>';
            })
            ->rawColumns(['cocher'])
            ->make(true);
    }

    public function getdataBonSM(Request $request)
    {
        $id = $request->get('id');
        $idStock = DB::table('stock_sites')->where('site', $id)->pluck('id');

        $bonsms = DB::table('bon_sortie_mags')
            ->join('sites', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.valide', 'bon_sortie_mags.id', 'bon_sortie_mags.created_at', 'users.name', 'users.lastname', 'sites.nomSite')
            ->where('bon_sortie_mags.idSite', $id)
            ->where('bon_sortie_mags.idStock', $idStock[0])
            ->orderBy('bon_sortie_mags.created_at', 'desc');

        return Datatables::of($bonsms)
            ->addColumn('action', function ($bonsms) {

                if ($bonsms->valide == false) {

                    return '' . Form::open(['route' => ['bonssortiemags.destroybon', $bonsms->id], 'onsubmit' => "if(!confirm('Voulez vraiment supprimer le bon de sortie ?')){return false;}", 'method' => 'delete']) . '' . csrf_field() . '
            
                
                <a data-toggle="tooltipB" title="Afficher les détails" class="btn btn-info " data-original-title title href="' . route("bonssortiemags.showbonsortiedet", $bonsms->id) . '"> <i class="material-icons">visibility</i> </a>
                <button data-toggle="tooltipB" title="Valider le bon"  rel="tooltip"  id="btn_pop" type="button"  class="btnclick btn btn-success " data-original-title title > <i class="material-icons">done</i> </button>
                <a data-toggle="tooltipB" title="Imprimer" rel="tooltip" class="btnclick btn btn-danger " data-original-title title href="' . route("bonssortiemags.Printbonsm", $bonsms->id) . '"> <i class="material-icons">print</i> </a>
                <a data-toggle="tooltipB" title="Modifier" rel="tooltip" class="btn btn-success   edits" data-original-title title href="' . route("bonssortiemags.edit", $bonsms->id) . '"> <i class="material-icons">edit</i> </a>
                <a data-toggle="tooltipB" title="PDF" class="btn btn-warning  " class="btn btn-success  "data-original-title title href="' . route("bonssortiemags.PDFbonsm", $bonsms->id) . '"> <i class="material-icons">picture_as_pdf</i> </a>
                <a data-toggle="tooltipB" title="Fichier Excel" class="btn btn-success  " data-original-title title href="' . route("bonssortiemags.EXCELbonsm", $bonsms->id) . '"> EX </a>
                <button data-toggle="tooltipB" title="Suprimer" id="btn_submit" type="submit" rel="tooltip" class="btn btn-danger   remove" data-original-title title><i class="material-icons">close</i></button>'

                        . Form::close();
                } else {
                    return '
                <a data-toggle="tooltipB" title="Afficher les détails" class="btn btn-info " data-original-title title href="' . route("bonssortiemags.showbonsortiedet", $bonsms->id) . '"> <i class="material-icons">visibility</i> </a>
                <button  id="btn_pop_dev" data-toggle="tooltipB" title="dévalider le bon" type="button" rel="tooltip" class="btnclick btn btn-danger " data-original-title title > <i class="material-icons">undo</i> </button>
                <a data-toggle="tooltipB" title="Imprimer" rel="tooltip" class="btnclick btn btn-danger " data-original-title title href="' . route("bonssortiemags.Printbonsm", $bonsms->id) . '"> <i class="material-icons">print</i> </a>
                <a data-toggle="tooltipB" title="PDF" class="btn btn-warning  " class="btn btn-success  "data-original-title title href="' . route("bonssortiemags.PDFbonsm", $bonsms->id) . '"> <i class="material-icons">picture_as_pdf</i> </a>
                <a data-toggle="tooltipB" title="Fichier Excel" class="btn btn-success  " data-original-title title href="' . route("bonssortiemags.EXCELbonsm", $bonsms->id) . '"> EX </a>
                ';
                }
            })

            ->rawColumns(['action'])
            ->make(true);
    }


    public function getdataproductSMC(Request $request)
    {
        $tab = $request->get('tab');
        $id = $request->get('id');

        if ($tab) {

            $idStock = DB::table('stock_sites')
                ->select('id')
                ->where('stock_sites.site', $id)
                ->pluck('id');

            $stocks = DB::table('stocks')
                ->join('products', 'stocks.numprod', '=', 'products.id')
                ->join('stock_sites', 'stock_sites.id', '=', 'stocks.id')
                ->select('stocks.qteAchat', 'stocks.prixAchat', 'products.designation', 'products.id', 'products.codebarre', 'products.photo')
                ->where('stocks.id', $idStock[0])
                ->whereNotIn('products.id', $tab);
        } else {

            $idStock = DB::table('stock_sites')
                ->select('id')
                ->where('stock_sites.site', $id)
                ->pluck('id');

            $stocks = DB::table('stocks')
                ->join('products', 'stocks.numprod', '=', 'products.id')
                ->join('stock_sites', 'stock_sites.id', '=', 'stocks.id')
                ->select('stocks.qteAchat', 'stocks.prixAchat', 'products.designation', 'products.id', 'products.codebarre', 'products.photo')
                ->where('stocks.id', $idStock[0]);
        }

        return Datatables::of($stocks)
            ->addColumn('cocher', function ($stocks) {

                return '<div class="checkbox-radios"> <div class="form-check"> <label class="form-check-label"> <input class="form-check-input" type="checkbox" name="produitid2[]" value="{{' . $stocks->id . '}}"> <span class="form-check-sign"><span class="check"></span></span></label></div></div>';
            })
            ->addColumn('designationPhoto', function ($stocks) {
                return '<a   data-toggle="tooltip" title="<img height=100 width=100 src=' . asset("fileproduit/" . $stocks->photo) . '>"/>
            ' . $stocks->designation . '
            </a>
            ';
            })
            ->rawColumns(['cocher', 'designationPhoto'])
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sites = Site::all();
        return view('bonssortiemags.index', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */

    public function create(Site $site)
    {
        $idstock = DB::table('stock_sites')
            ->select('id')
            ->where('stock_sites.site', $site->id)
            ->get();

        $stocks = DB::table('stocks')
            ->join('products', 'stocks.numprod', '=', 'products.id')
            ->join('stock_sites', 'stock_sites.id', '=', 'stocks.id')
            ->select('stocks.*', 'products.designation')
            ->where('stocks.id', $idstock[0]->id)
            ->get();
        return view('bonssortiemags.create', compact('stocks'));
    }


    public function createbonsortie(Site $site)
    {
        $idstock = DB::table('stock_sites')
            ->select('id')
            ->where('stock_sites.site', $site->id)
            ->get();

        $stocks = DB::table('stocks')
            ->join('products', 'stocks.numprod', '=', 'products.id')
            ->join('stock_sites', 'stock_sites.id', '=', 'stocks.id')
            ->select('stocks.id AS idStock', 'stocks.prixAchat AS prixAchatP', 'stocks.qteAchat AS qteAchatP', 'products.*')
            ->where('stocks.id', $idstock[0]->id)
            ->get();

        $sitename = $site->nomSite;
        $sendids = $site->id;
        $sendidst = $idstock[0]->id;

        $destinations = DB::table('destinations')

            ->select('destination')
            ->where('idSite', $site->id)
            ->get();

        return view('bonssortiemags.create', compact('stocks', 'sendids', 'sendidst', 'sitename', 'destinations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storebonsortie(Request $request, int $site, int $stock, int $sup)
    {
        $this->validate($request, [
            'date' => 'required',
            'produitid' => 'required',
            'qteAchat' => 'required',
            'destination' => 'required',
        ]);


        //* test request produit Empty 
        $i = 0;
        $trans = 0;

        $numbon = 0;
        $prixtotal = 0;

        $numtest = DB::table('bon_sortie_mags')
            ->select('numbonsm')
            ->where('idSite', $site)
            ->where('idStock', $stock)
            ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->latest('created_at')->first();

        if ($numtest == null) {
            $numbon = 'BS1-' . date("H") . date("i") . date("s") . date("d") . date("m") . date("Y") . '/' . $site;
        } else {
            if (strlen($numtest->numbonsm) == 1) {
                $numtest = intval($numtest->numbonsm) + 1;
                $numbon = 'BS' . $numtest . '-' . date("H") . date("i") . date("s") . date("d") . date("m") . date("Y") . '/' . $site;
            } else {
                $incr = explode('-', $numtest->numbonsm)[0];
                $inc = substr($incr, 2);
                $incr = intval($inc) + 1;
                $numbon = 'BS' . $incr . '-' . date("H") . date("i") . date("s") . date("d") . date("m") . date("Y") . '/' . $site;
            }
        }

        DB::beginTransaction();

        try {

            $d = date('Y-m-d');
            $bsm = BonSortieMag::create([
                'idSite' => $site,
                'idStock' => $stock,
                'numbonsm' => $numbon,
                'date' => $request['date'],
                'superviseur' => $sup,
                'destination' => $request['destination'],
                'prixtotal' => 0,
                'valide' => false,
                'observation' => $request['observation']

            ]);

            $insertedId = $bsm->id;

            foreach ($request->input("produitid") as $idprod) {
                $prixProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $stock)->pluck('prixAchat');
                BonSortieMagDet::create([
                    'idbon' =>  $insertedId,
                    'numprod' => $idprod,
                    'qteAchat' => $request['qteAchat'][$i],
                    'prixAchat' => $prixProd[0]
                ]);

                $qteProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $stock)->pluck('qteAchat');

                DB::table('stocks')
                    ->where('numprod', $idprod)
                    ->where('id', $stock)
                    ->update(['qteAchat' => (float)$qteProd[0] - (float)$request['qteAchat'][$i]]); //int doit etre float
                $i++;
            }

            $bonEntrersPQ =  DB::table('bon_sortie_mag_dets')

                ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
                ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
                ->select()
                ->where('bon_sortie_mag_dets.idbon', $bsm->id)
                ->sum(DB::raw('bon_sortie_mag_dets.qteAchat * bon_sortie_mag_dets.prixAchat '));

            DB::table('bon_sortie_mags')
                ->where('id', $bsm->id)
                ->update(['prixtotal' => $bonEntrersPQ]);

            $trans = 1;

            DB::commit();
            //all good

            event(new addBon_S(Auth::user(), $bsm->id, '/laravelF/public/bonssortiemags/listebonssortiemags/detail/' . $bsm->id));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('bonssortiemags.index')
                ->with('danger', $e->getMessage());
        }

        if ($trans == 1) {
            return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $site])
                ->with('success', 'Le bon sortie mag est crée avec sucsès.');
        } else {
            return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $site])
                ->with('warning', 'Erreur de creation de bons de sortie.');
        }
    }


    public function indexbonssortiemags(Site $site)
    {

        $idStock = DB::table('stock_sites')->where('site', $site->id)->pluck('id');

        $bonsms = DB::table('bon_sortie_mags')
            ->join('sites', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.*', 'users.name AS nomuser', 'users.lastname AS prenomuser', 'sites.nomSite')
            ->where('idSite', $site->id)
            ->where('idStock', $idStock[0])
            ->get();

        return view('bonssortiemags.listebonssm', compact('bonsms', 'site'));
    }

    public function getdataBonsms(Request $request)
    {
        $id = $request->get('id');

        $bonsms = DB::table('bon_sortie_mags')
            ->join('sites', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.id', 'bon_sortie_mags.created_at', 'users.name', 'users.lastname', 'sites.nomSite')
            ->where('idSite', $id);

        return Datatables::of($bonsms)

            ->addColumn('action', function ($bonsms) {
                return '' . Form::open(['route' => ['bonssortiemags.destroybon',  $bonsms->id]]) . '' . csrf_field() . '
            
                <button id="btn_submit" type="submit" rel="tooltip" class="btn btn-danger" data-original-title title><i class="material-icons">close</i></button>
                <a rel="tooltip" class="btn btn-info" data-original-title title href="' . route("bonssortiemags.showbonsortiedet", $bonsms->id) . '"> <i class="material-icons">visibility</i> </a>
                <a rel="tooltip" class="btn btn-success" data-original-title title href="' . route("bonssortiemags.edit", $bonsms->id) . '"> <i class="material-icons">edit</i> </a>'

                    . Form::close();
            })

            ->rawColumns(['action'])
            ->make(true);
    }


    public function showbonsortiedet(int $id)
    {
        $supdet = DB::table('bon_sortie_mags')
            ->join('sites', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.valide', 'bon_sortie_mags.observation', 'bon_sortie_mags.idSite AS idsbon', 'bon_sortie_mags.id AS idbonsm', 'bon_sortie_mags.date AS datebon', 'bon_sortie_mags.prixtotal', 'bon_sortie_mags.numbonsm', 'bon_sortie_mags.destination', 'users.name', 'users.lastname', 'bon_sortie_mags.created_at', 'sites.nomSite')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $useru = DB::table('bon_sortie_mags')
            ->join('users AS userU', 'bon_sortie_mags.updateby', '=', 'userU.id')
            ->select('userU.name AS nameu', 'userU.lastname AS lastnameu')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $userv = DB::table('bon_sortie_mags')
            ->join('users AS userV', 'bon_sortie_mags.validateby', '=', 'userV.id')
            ->select('userV.name AS namev', 'userV.lastname AS lastnamev')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $dateheur = explode(" ", $supdet[0]->created_at);
        $datetabjma = explode("-", $dateheur[0]);

        $bsmdets = DB::table('bon_sortie_mag_dets')
            ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
            ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
            ->join('unites', 'unites.id', '=', 'products.unite')
            ->join('marques', 'marques.id', '=', 'products.marque')
            ->select('bon_sortie_mag_dets.numprod AS idprod', 'bon_sortie_mag_dets.idbon', 'bon_sortie_mag_dets.qteAchat', 'bon_sortie_mag_dets.prixAchat', 'products.designation', 'marques.nom AS marque', 'unites.nom AS unite', 'products.photo')
            ->where('bon_sortie_mag_dets.idbon', $id)
            ->get();

        return view('bonssortiemags.bonsmdet', compact('bsmdets', 'supdet', 'dateheur', 'datetabjma', 'useru', 'userv'));
    }

    public function PDFbonsm(int $id)
    {

        $supdet = DB::table('bon_sortie_mags')
            ->join('sites', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.prixtotal', 'bon_sortie_mags.numbonsm', 'users.name', 'users.lastname', 'bon_sortie_mags.created_at', 'sites.nomSite', 'sites.id AS idS')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $dateheur = explode(" ", $supdet[0]->created_at);
        $datetabjma = explode("-", $dateheur[0]);

        $bsmdets = DB::table('bon_sortie_mag_dets')
            ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
            ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
            ->join('unites', 'unites.id', '=', 'products.unite')
            ->join('marques', 'marques.id', '=', 'products.marque')
            ->select('bon_sortie_mag_dets.prixAchat AS prixachatP', 'bon_sortie_mag_dets.idbon', 'bon_sortie_mag_dets.qteAchat', 'products.designation', 'marques.nom AS marque', 'unites.nom AS unite')
            ->where('bon_sortie_mag_dets.idbon', $id)
            ->get();
        $datenow = date("Y-m-d H:i:s");
        $d = "defaultlogo.jpg";

        $pdf = PDF::loadView('bonssortiemags.pdfbonsm', compact('bsmdets', 'supdet', 'dateheur', 'datetabjma', 'datenow', 'd'));
        return $pdf->download('bonsm' . $supdet[0]->numbonsm . '-' . $datetabjma[2] . $datetabjma[1] . $datetabjma[0] . '.pdf');
        return view('bonssortiemags.pdfbonsm', compact('bsmdets', 'supdet', 'dateheur', 'datetabjma', 'datenow', 'd'));
    }
    public function EXCELbonsm(int $id)
    {


        $supdet = DB::table('bon_sortie_mags')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.numbonsm', 'bon_sortie_mags.created_at', 'users.name', 'users.lastname', 'bon_sortie_mags.prixtotal', 'bon_sortie_mags.destination', 'bon_sortie_mags.idSite')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $site = DB::table('sites')
            ->select()
            ->where('id', $supdet[0]->idSite)
            ->get();

        $dateheur = explode(" ", $supdet[0]->created_at);
        $datetabjma = explode("-", $dateheur[0]);

        $nomexcel = $supdet[0]->numbonsm . '-' . $datetabjma[2] . $datetabjma[1] . $datetabjma[0];

        $bsmdets = DB::table('bon_sortie_mag_dets')

            ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
            ->join('stocks', function ($join) {
                $join->on('bon_sortie_mags.idStock', '=', 'stocks.id')
                    ->on('bon_sortie_mag_dets.numprod', '=', 'stocks.numprod');
            })

            ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')

            ->select('products.id AS idpro', 'products.designation', 'bon_sortie_mag_dets.qteAchat', 'stocks.prixAchat AS prixachatP', DB::raw('(bon_sortie_mag_dets.qteAchat * stocks.prixAchat) AS prixT'))
            ->where('bon_sortie_mag_dets.idbon', $id)
            ->get();
        $nbrP = count($bsmdets);
        $bonArray = [];

        // Define the Excel spreadsheet headers
        $bonArray[] = ['Id', 'Designation', 'Qantite', 'Prix U'];

        $test =   BonSortieMagDet::all();

        // Generate and return the spreadsheet
        return (new BonSortieMagExport($id, $nomexcel, $supdet[0]->created_at, $supdet[0]->name, $supdet[0]->lastname, $nbrP, $supdet[0]->prixtotal, $supdet[0]->destination, $site[0]->nomSite))->download('BonSM' . $nomexcel . '.xlsx');
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

        $bon = DB::table('bon_sortie_mags')
            ->select()
            ->where('bon_sortie_mags.id', $id)
            ->get();

        if ($bon[0]->valide) {
            return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $bon[0]->idSite])
                ->with('danger', 'Vous ne pouvez pas modifier un bon VALIDE');
        }

        $sites = DB::table('sites')
            ->join('bon_sortie_mags', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->select('sites.nomSite AS noms', 'sites.id AS idSite')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $idst = DB::table('bon_sortie_mags')
            ->select()
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $produits = DB::table('stocks')
            ->join('products', 'stocks.numprod', '=', 'products.id')
            ->select('products.id as prodid', 'products.designation as proddesingnation', 'products.codebarre as prodcodebarre')
            ->where('stocks.id', $idst[0]->idStock)
            ->whereNotIn('stocks.numprod', function ($query) use ($id, $idst) {
                $query->select('numprod')->from('bon_sortie_mag_dets')
                    ->where('bon_sortie_mag_dets.idbon', $id);
            })->get();

        $produitBons = DB::table('bon_sortie_mag_dets')
            ->join('products', 'products.id', '=', 'bon_sortie_mag_dets.numprod')
            ->select('bon_sortie_mag_dets.idbon AS idbondet', 'bon_sortie_mag_dets.numprod AS nprod', 'bon_sortie_mag_dets.prixAchat AS prixAchatdet', 'bon_sortie_mag_dets.qteAchat AS qteAchatdet', 'products.designation', 'products.codebarre')
            ->where('bon_sortie_mag_dets.idbon', $id)
            ->get();

        return view('bonssortiemags.edit', compact('id', 'sites', 'produits', 'produitBons', 'bon'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatebon(Request $request, $id)
    {
        $this->validate($request, [
            'produitid' => 'required',
            'qteAchat' => 'required',
        ]);

        $trans = 0;

        DB::beginTransaction();
        try {

            $user = Auth::user();

            $produits = DB::table('bon_sortie_mag_dets')->select()->where('idbon', $id)->get();

            foreach ($produits as $produit) {

                $qte = DB::table('stocks')->select('qteAchat')->where('numprod', $produit->numprod)->where('id', $request['idSite'])->pluck('qteAchat');

                if ($qte->count() != 0) {
                    DB::table('stocks')
                        ->where('numprod', $produit->numprod)
                        ->where('id', $request['idSite'])
                        ->update(['qteAchat' => (float)$qte[0] + (float)$produit->qteAchat]);
                }
            }

            $bonEntrerdet = DB::table('bon_sortie_mag_dets')->where('idbon', $id)->delete();
            //fin sup
            //creation bon et modification de stock
            $idStock = DB::table('stock_sites')->where('site', $request['idSite'])->pluck('id')[0];
            $i = 0;

            foreach ($request->input("produitid") as $idprod) {
                $idprodAB = $produits->where('numprod', $idprod);
                if ($idprodAB->isEmpty()) {
                    $prixProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $request['idSite'])->pluck('prixAchat')[0];
                } else
                    $prixProd = $idprodAB->first()->prixAchat;


                BonSortieMagDet::create([
                    'idbon' => $id,
                    'numprod' => $idprod,
                    'qteAchat' => $request['qteAchat'][$i],
                    'prixAchat' => $prixProd,
                ]);
                $testProd = '';
                $testProd =  DB::table('stocks')->where('numprod', $idprod)->where('id', $request['idSite'])->pluck('numprod');
                $qteProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $request['idSite'])->pluck('qteAchat');
                $prixProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $request['idSite'])->pluck('prixAchat');

                DB::table('stocks')
                    ->where('numprod', $idprod)
                    ->where('id', $idStock)
                    ->update(['qteAchat' => (float)$qteProd[0] - (float)$request['qteAchat'][$i]]);
                $i++;
            }

            $bonSortiePQ =  DB::table('bon_sortie_mag_dets')
                ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
                ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
                ->select()
                ->where('bon_sortie_mag_dets.idbon', $id)
                ->sum(DB::raw('bon_sortie_mag_dets.qteAchat * bon_sortie_mag_dets.prixAchat '));

            DB::table('bon_sortie_mags')
                ->where('id', $id)
                ->update(['prixtotal' => $bonSortiePQ, 'observation' => $request['observation'], 'updateby' => $user->id, 'updated_at' => date("Y-m-d H:i:s")]);


            $trans = 1;

            DB::commit();
        } catch (\Exception $e) {

            DB::rollback();

            return redirect()->route('bonssortiemags.index')
                ->with('warning', $e->getMessage());
        }


        if ($trans == 1) {
            return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $request['idSite']])
                ->with('success', 'Le bon sortie mag a ete modifie avec sucsès.');
        } else {
            return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $request['idSite']])
                ->with('warning', 'Erreur de modification de bons de sortie.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(BonSortieMag $bon)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\BonSortieMag $bon
     * @return \Illuminate\Http\Response
     */

    public function destroybon(BonSortieMag $bon)
    {


        $bsmdets = DB::table('bon_sortie_mag_dets')
            ->select()
            ->where('bon_sortie_mag_dets.idbon', $bon->id)
            ->get();


        foreach ($bsmdets as $bsmdet) {

            $qteachatA = DB::table('stocks')
                ->select('qteAchat')
                ->where('stocks.numprod', $bsmdet->numprod)
                ->where('stocks.id', $bon->idStock)
                ->get();

            $qteB = (float) $bsmdet->qteAchat;

            DB::table('stocks')
                ->where('numprod', $bsmdet->numprod)
                ->where('id', $bon->idStock)
                ->update(['qteAchat' => (float) $qteachatA[0]->qteAchat +  $qteB]);
        }

        $bon->delete();

        return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $bon->idSite])
            ->with('success', 'Bon de sortie supprimer avec succes');
    }


    public function Printbonsm(int $id)
    {


        $supdet = DB::table('bon_sortie_mags')
            ->join('sites', 'bon_sortie_mags.idSite', '=', 'sites.id')
            ->join('users', 'bon_sortie_mags.superviseur', '=', 'users.id')
            ->select('bon_sortie_mags.destination', 'bon_sortie_mags.date', 'bon_sortie_mags.valide', 'bon_sortie_mags.observation', 'bon_sortie_mags.prixtotal', 'bon_sortie_mags.numbonsm', 'users.name', 'users.lastname', 'bon_sortie_mags.created_at', 'sites.nomSite', 'sites.id AS idS')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $useru = DB::table('bon_sortie_mags')
            ->join('users AS userU', 'bon_sortie_mags.updateby', '=', 'userU.id')
            ->select('userU.name AS nameu', 'userU.lastname AS lastnameu')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $userv = DB::table('bon_sortie_mags')
            ->join('users AS userV', 'bon_sortie_mags.validateby', '=', 'userV.id')
            ->select('userV.name AS namev', 'userV.lastname AS lastnamev')
            ->where('bon_sortie_mags.id', $id)
            ->get();

        $dateheur = explode(" ", $supdet[0]->created_at);
        $datetabjma = explode("-", $dateheur[0]);

        $bsmdets = DB::table('bon_sortie_mag_dets')
            ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
            ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
            ->join('marques', 'marques.id', '=', 'products.marque')
            ->join('unites', 'unites.id', '=', 'products.unite')
            ->select('bon_sortie_mag_dets.prixAchat AS prixachatP', 'bon_sortie_mag_dets.idbon', 'bon_sortie_mag_dets.qteAchat', 'products.designation', 'marques.nom AS marque', 'unites.nom AS unite', 'products.id')
            ->where('bon_sortie_mag_dets.idbon', $id)
            ->get();



        $datenow = date("Y-m-d H:i:s");
        $d = "defaultlogolite.jpg";

        return view('bonssortiemags.printbonsm', compact('bsmdets', 'supdet', 'dateheur', 'datetabjma', 'datenow', 'd', 'useru', 'userv'));
    }





    /////import bon sortie
    public function importExcelBonssm()
    {

        return view('bonssortiemags.importexcelbonsm');
    }


    public function createbonsmimport(Request $request)
    {
        request()->validate([
            'import_file' => 'required|mimes:xlsx,xls',
        ]);

        if ($request->hasFile('import_file')) {

            $imported = (new BonSortieImport)->toCollection(request()->file('import_file'));
        }

        $tabbonen = [];
        $tabnumprod = [];
        for ($i = 0; $i < $imported[0]->count(); $i++) {

            $row = $imported[0][$i][0];
        }

        for ($i = 7; $i < $imported[0]->count(); $i++) {
            $row = $imported[0][$i][0];

            if ($row == "Code article") {

                for ($j = 8; $j < $imported[0]->count(); $j++) {
                    $row2 = $imported[0][$j][3];
                    if ($row2 != "Total" && $row2 != null) {
                        $tabbonen[] = ['numprod' => $imported[0][$j][0], 'des' => $imported[0][$j][1], 'qteSortie' => $imported[0][$j][3], 'prixAchat' => $imported[0][$j][4]];
                        $tabnumprod[] = $imported[0][$j][0];
                    }
                }
            }
        }


        //dd($imported);
        $produits = DB::table('products')
            ->select()
            ->whereIn('products.id', $tabnumprod)->get();

        $produitsnew = [];

        for ($l = 0; $l < count($tabbonen); $l++) {

            $idimpor = (int)$tabbonen[$l]["numprod"];

            if ($produits->where('id', $idimpor)->first()) {
                $produit = $produits->where('id', $idimpor)->first();

                if ($produit->designation != $tabbonen[$l]["des"]) {

                    return redirect()->route('bonssortiemags.importExcelBonssm')
                        ->with('danger', 'Les designation produit ne corresponde pas au code article (code produit: ' . $tabbonen[$l]["numprod"] . ').');
                } else {
                    $produitsnew[] = [
                        'id' => $produit->id, 'designation' => $produit->designation, 'marque' =>  $produit->marque, 'modele' =>  $produit->modele, 'unite' =>  $produit->unite,
                        'type' =>  $produit->type, 'categorie' =>  $produit->categorie, 'codebarre' =>  $produit->codebarre, 'photo' =>  $produit->photo, 'qteSortie' => $tabbonen[$l]["qteSortie"], 'prixAchat' => $tabbonen[$l]["prixAchat"]
                    ];
                }
            }
        }


        $date = $imported[0][5][3];
        $idsite = (int)$imported[0][5][6];
        $iduser = (int)$imported[0][5][7];
        $dest = (int)$imported[0][5][2];

        $sites = DB::table('sites')
            ->select('sites.nomSite AS noms', 'sites.id AS idSite')
            ->where('sites.id', $idsite)
            ->get();


        $destination = DB::table('destinations')
            ->select('destinations.destination', 'destinations.id AS iddest', 'destinations.idSite AS idsdest')
            ->where('destinations.id', $dest)
            ->where('destinations.idSite', $idsite)
            ->get();

        $user = DB::table('users')
            ->select('id', 'name', 'lastname', 'email', 'image', 'created_at', 'updated_at')
            ->where('users.id', $iduser)
            ->get();

        if ($destination->isEmpty()) {

            return redirect()->route('bonssortiemags.importExcelBonssm')
                ->with('danger', 'Erreur dans le code de destination.');
        } else {
            if ($sites->isEmpty()) {

                return redirect()->route('bonssortiemags.importExcelBonssm')
                    ->with('danger', 'Le code du site n\'existe pas.');
            } else {
                if ($user->isEmpty()) {
                    return redirect()->route('bonssortiemags.importExcelBonssm')
                        ->with('danger', 'Le code de l\'utilisateur n\'existe pas.');
                }
            }
        }

        return view('bonssortiemags.validebonsmimport', compact('destination', 'sites', 'user', 'produitsnew', 'date', 'idsite', 'iduser'));
    }

    //preview bon
    public function bonsmimportcharge(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'import_file' => 'required|mimes:xlsx,xls',
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => "errorupload", 'msg' => $validator->errors()->all()[0]]);
        }

        if ($request->hasFile('import_file')) {

            $imported = (new BonSortieImport)->toCollection(request()->file('import_file'));
        }

        $bon = $imported[0][0][1];

        if ($bon != "BON SORTIE") {
            return response()->json(['status' => "errorupload", 'msg' => "Le fichier excel n'est pas aux normes."]);
        }

        $tabbonen = [];
        $tabnumprod = [];


        for ($i = 7; $i < $imported[0]->count(); $i++) {
            $row = $imported[0][$i][0];

            if ($row == "Code article") {

                for ($j = 8; $j < $imported[0]->count(); $j++) {
                    $row2 = $imported[0][$j][3];
                    if ($row2 != "Total" && $row2 != null) {
                        $tabbonen[] = ['numprod' => $imported[0][$j][0], 'des' => $imported[0][$j][1], 'qteSortie' => $imported[0][$j][3], 'prixAchat' => $imported[0][$j][4]];
                        $tabnumprod[] = $imported[0][$j][0];
                    }
                }
            }
        }

        $produits = DB::table('products')
            ->join('marques', 'marques.id', '=', 'products.marque')
            ->join('types', 'types.id', '=', 'products.type')
            ->join('categories', 'categories.id', '=', 'products.categorie')
            ->join('unites', 'unites.id', '=', 'products.unite')
            ->select(['products.id', 'products.designation', 'marques.nom AS marque', 'products.modele', 'types.nom AS type', 'categories.nom AS categorie', 'products.codebarre', 'unites.nom AS unite', 'products.photo'])
            ->whereIn('products.id', $tabnumprod)->get();

        $produitsnew = [];

        for ($l = 0; $l < count($tabbonen); $l++) {
            $idimpor = (int)$tabbonen[$l]["numprod"];

            if ($produits->where('id', $idimpor)->first()) {
                $produit = $produits->where('id', $idimpor)->first();

                if ($produit->designation != $tabbonen[$l]["des"]) {
                    $code = $tabbonen[$l]["numprod"];

                    $response = [
                        'status' => "errorupload",
                        'msg' => "Les designations produit ne corresponde pas au code article (code: $code).",
                    ];
                    return \Response::json($response);
                } else {
                    $produitsnew[] = [
                        'id' => $produit->id, 'designation' => $produit->designation, 'marque' =>  $produit->marque, 'modele' =>  $produit->modele, 'unite' =>  $produit->unite,
                        'type' =>  $produit->type, 'categorie' =>  $produit->categorie, 'codebarre' =>  $produit->codebarre, 'photo' =>  $produit->photo, 'qteSortie' => $tabbonen[$l]["qteSortie"], 'prixAchat' => $tabbonen[$l]["prixAchat"]
                    ];
                }
            }
        }

        $date = $imported[0][5][3];
        $idsite = (int)$imported[0][5][6];
        $iduser = (int)$imported[0][5][7];
        $dest = (int)$imported[0][5][2];

        $sites = DB::table('sites')
            ->select('sites.nomSite AS noms', 'sites.id AS idSite')
            ->where('sites.id', $idsite)
            ->get();


        $destination = DB::table('destinations')
            ->select('destinations.destination', 'destinations.id AS iddest', 'destinations.idSite AS idsdest')
            ->where('destinations.id', $dest)
            ->where('destinations.idSite', $idsite)
            ->get();

        $user = DB::table('users')
            ->select('id', 'name', 'lastname', 'email', 'image', 'created_at', 'updated_at')
            ->where('users.id', $iduser)
            ->get();

        if ($destination->isEmpty()) {
            $response = [
                'status' => "errorupload",
                'msg' => "Erreur dans le code de destination.",
            ];
            return \Response::json($response);
        } else {
            if ($sites->isEmpty()) {
                $response = [
                    'status' => "errorupload",
                    'msg' => "Le code du site n'existe pas.",
                ];
                return \Response::json($response);
            } else {
                if ($user->isEmpty()) {
                    $response = [
                        'status' => "errorupload",
                        'msg' => "Le code de l'utilisateur n'existe pas.",
                    ];
                    return \Response::json($response);
                }
            }
        }

        return view('bonssortiemags.bonsortieimportcharger', compact('destination', 'sites', 'user', 'produitsnew', 'date', 'idsite', 'iduser'));
    }

    //store import excel
    public function storebonsortieimport(Request $request)
    {
        //dd($request);

        $validator = \Validator::make($request->all(), [
            'import_file' => 'required|mimes:xlsx,xls',
            'produitid' => 'required',
            'qteAchat' => 'required',
            'destination' => 'required',
            'idSite' => 'required',
            'date' => 'required',
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => "error", 'msg' => $validator->errors()->all()[0]]);
        }


        $user = Auth::user();
        $site = $request['idSite'];

        $stock = DB::table('stock_sites')
            ->where('stock_sites.site', $site)
            ->get()[0]->id;

        $sup = $request['superviseur'];
        $i = 0;
        $trans = 0;
        $numbon = 0;
        $prixtotal = 0;

        $bonsmsN = DB::table('bon_sortie_mags')
            ->select('numbonsm')
            ->where('idSite', $site)
            ->where('idStock', $stock)
            ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->max('numbonsm');


        if ($bonsmsN != 0) {
            $numbon = $bonsmsN + 1;
        } else {
            $numbon = 1;
        }

        DB::beginTransaction();
        try {

            $d = $request['date'];
            $bsm = BonSortieMag::create([
                'idSite' => $site,
                'idStock' => $stock,
                'numbonsm' => $numbon,
                'date' => $request['date'],
                'superviseur' => $sup,
                'destination' => $request['destination'],
                'prixtotal' => 0,
            ]);

            //excel bon sortie upload
            if ($request['import_file'] != null) {
                $excel = $request->file('import_file');
                $filename = $excel->getClientOriginalName();
                $extension = $excel->getClientOriginalExtension();
                $name = date("Y-m-d H:i:s");
                $filenameb = time() . $excel->getClientOriginalName();
                Storage::disk('public')->putFileAs('excelimporter/bonsortie/' . $site . '/' . $bsm->id, $excel, $filenameb);

                BonHorsLigne::create([
                    'idSite' => $site,
                    'typebon' => "BS",
                    'iduser' => $user->id,
                    'idbon' => $bsm->id,
                    'nomfichier' => $filenameb
                ]);
            }

            $insertedId = $bsm->id;
            foreach ($request->input("produitid") as $idprod) {
                $prixProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $stock)->pluck('prixAchat');
                BonSortieMagDet::create([
                    'idbon' =>  $insertedId,
                    'numprod' => $idprod,
                    'qteAchat' => $request['qteAchat'][$i],
                    'prixAchat' => $prixProd[0]
                ]);

                $qteProd = DB::table('stocks')->where('numprod', $idprod)->where('id', $stock)->pluck('qteAchat');

                DB::table('stocks')
                    ->where('numprod', $idprod)
                    ->where('id', $stock)
                    ->update(['qteAchat' => (float)$qteProd[0] - $request['qteAchat'][$i]]); //int doit etre float
                $i++;
            }

            $bonEntrersPQ =  DB::table('bon_sortie_mag_dets')
                ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
                ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
                ->select()
                ->where('bon_sortie_mag_dets.idbon', $bsm->id)

                ->sum(DB::raw('bon_sortie_mag_dets.qteAchat * bon_sortie_mag_dets.prixAchat '));

            DB::table('bon_sortie_mags')
                ->where('id', $bsm->id)
                ->update(['prixtotal' => $bonEntrersPQ]);

            $trans = 1;

            DB::commit();
            //all good
            event(new addBonS_up(Auth::user(), $bsm->id, '/laravelF/public/bonssortiemags/listebonssortiemags/detail/' . $bsm->id));
        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => "error",
                'msg' => $e->getMessage(),
            ];
            return \Response::json($response);
        }


        if ($trans == 1) {
            $response = [
                'status' => "success",
                'msg' => 'Le bon de sortie est crée avec succès.',
            ];
            return \Response::json($response);
        } else {
            $response = [
                'status' => "error",
                'msg' => 'Erreur de creation de bon de sortie.',
            ];
            return \Response::json($response);
        }
    }


    public function updatebonprod(Request $request, int $id)
    {
        $this->validate($request, [
            'produitid' => 'required',
            'qteAchat' => 'required',
        ]);

        $trans = 0;
        $datecreationbon = DB::table('bon_sortie_mags')->select()->where('id', $id)->get();

        DB::beginTransaction();
        try {

            $produit = DB::table('bon_sortie_mag_dets')->select()->where('idbon', $id)->where('numprod', $request['produitid'])->get();

            $idStock = DB::table('stock_sites')->where('site', $request['idSite'])->pluck('id')[0];

            $qte = DB::table('stocks')->select('qteAchat')->where('numprod', $produit[0]->numprod)->where('id', $request['idSite'])->pluck('qteAchat');

            if ($qte->count() != 0) {

                DB::table('stocks')
                    ->where('numprod', $request['produitid'])
                    ->where('id', $request['idSite'])
                    ->update(['qteAchat' => (float)$qte[0] + (float)$produit[0]->qteAchat, 'updated_at' => date("Y-m-d H:i:s")]);
            }

            $qteProd = DB::table('stocks')->where('numprod', $request['produitid'])->where('id', $idStock)->pluck('qteAchat');
            $bonEntrerdetup = DB::table('bon_sortie_mag_dets')
                ->where('numprod', $request['produitid'])
                ->where('idbon', $id)
                ->update(['qteAchat' => (float)$request['qteAchat'], 'updated_at' => date("Y-m-d H:i:s")]);

            DB::table('stocks')
                ->where('numprod', $request['produitid'])
                ->where('id', $idStock)
                ->update(['qteAchat' => (float)$qteProd[0] - (float)$request['qteAchat'], 'updated_at' => date("Y-m-d H:i:s")]);

            $bonSortiePQ =  DB::table('bon_sortie_mag_dets')
                ->join('bon_sortie_mags', 'bon_sortie_mags.id', '=', 'bon_sortie_mag_dets.idbon')
                ->join('products', 'bon_sortie_mag_dets.numprod', '=', 'products.id')
                ->select()
                ->where('bon_sortie_mag_dets.idbon', $id)
                ->sum(DB::raw('bon_sortie_mag_dets.qteAchat * bon_sortie_mag_dets.prixAchat '));

            DB::table('bon_sortie_mags')
                ->where('id', $id)
                ->update(['prixtotal' => $bonSortiePQ, 'updated_at' => date("Y-m-d H:i:s")]);


            $trans = 1;
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('bonssortiemags.showbonsortiedet', ['idbon' => $id])
                ->with('danger', $e->getMessage());
        }

        if ($trans == 1) {
            return redirect()->route('bonssortiemags.showbonsortiedet', ['idbon' => $id])
                ->with('success', 'Le bon de sortie est modifié avec succès.');
        } else
            return redirect()->route('bonssortiemags.showbonsortiedet', ['idbon' => $id])
                ->with('warnning', 'erreur dans la modification du bon de sortie.');
    }


    public function validerbs(Request $request)
    {
        $user = Auth::user();
        $idbs = $request['idbs'];

        $bssite = DB::table('bon_sortie_mags')
            ->select()
            ->where('bon_sortie_mags.id', $idbs)
            ->get();

        $bst = DB::table('bon_sortie_mags')
            ->select()
            ->where('bon_sortie_mags.id', $idbs)
            ->where('bon_sortie_mags.valide', true)
            ->get();

        if (!$bst->isEmpty()) {
            return redirect()->route('bonssortiemags.indexbonssortiemags', ['site' => $bst[0]->idSite])
                ->with('danger', 'Le bon de sortie est deja validé.');
        }

        DB::beginTransaction();
        try {
            DB::table('bon_sortie_mags')
                ->where('bon_sortie_mags.id', $idbs)
                ->update(['valide' => true, 'validateby' => $user->id, 'updated_at' => date("Y-m-d H:i:s")]);

            $trans = 1;
            DB::commit();
            //all good     

        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'id' => $idbs,
            ];
            return \Response::json($response);
        }

        if ($trans == 1) {
            $response = [
                'status' => 'success',
                'message' => 'Bon de sortie validé',
                'id' => $idbs,
            ];
            return \Response::json($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Erreur de validation du bon de sortie',
                'id' => $idbs,
            ];
            return \Response::json($response);
        }
    }


    public function devaliderbs(Request $request)
    {
        $user = Auth::user();
        $idbs = $request['idbs'];

        DB::beginTransaction();
        try {
            DB::table('bon_sortie_mags')
                ->where('bon_sortie_mags.id', $idbs)
                ->update(['valide' => false, 'validateby' => $user->id, 'updated_at' => date("Y-m-d H:i:s")]);

            $trans = 1;
            DB::commit();
            //all good     

        } catch (\Exception $e) {
            DB::rollback();
            $response = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'id' => $idbs,
            ];
            return \Response::json($response);
        }

        if ($trans == 1) {

            $response = [
                'status' => 'success',
                'message' => 'Bon de sortie devalidé',
                'id' => $idbs,
            ];
            return \Response::json($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Erreur de devalidation du bon de sortie',
                'id' => $idbs,
            ];
            return \Response::json($response);
        }
    }
}
