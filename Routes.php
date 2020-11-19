<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//route notification
Route::get('notification/deleteNotif/{id}', 'notificationController@deleteNotif')->name('deleteNotif2');
Route::get('notification/lus/{id}', 'notificationController@lus')->name('lus');
Route::get('ajaxnotification/lus', 'notificationController@ajaxlus')->name('ajaxlus');
Route::get('ajaxnotification/del', 'notificationController@ajaxdel')->name('ajaxdel');
//fin route notification

//Route profile
Route::get('profile', 'UserController@profile');
Route::post('profile', 'UserController@modifier_profile');
//fin Route Profile

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/acceuil', 'HomeController@indexcat')->name('indexcat');

Route::group(['middleware' => ['auth', 'role:Admin']], function () 
{
    ///POS
    Route::get('pos/createpos/{site}', 'PosController@createpos')->name('pos.createpos');
    Route::get('pos', 'PosController@index')->name('pos.index');
    Route::get('pos/htmlprodfromcat', 'PosController@htmlprodfromcat')->name('pos.htmlprodfromcat');
    ///POS

    Route::get('imageconvf/{idconv}/{filename}', function ($idconv, $filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/fileconv/' . $idconv . '/' . $filename);

        if (!File::exists($path)) 
        {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    Route::get('imageff/{idcf}/{filename}', function ($idff, $filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/fileff/' . $idff . '/' . $filename);
        if (!File::exists($path)) 
        {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    Route::get('imagecf/{idcf}/{filename}', function ($idcf, $filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/filecf/' . $idcf . '/' . $filename);

        if (!File::exists($path)) 
        {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    Route::get('imageop/{idop}/{filename}', function ($idop, $filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/fileop/' . $idop . '/' . $filename);
        if (!File::exists($path)) 
        {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    Route::get('filelogoadmin/{filename}', function ($filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/logo/' . $filename);
        if (!File::exists($path)) 
        {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });


    Route::get('filelogonoti/{filename}', function ($filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/iconNoti/BE/' . $filename);

        if (!File::exists($path)) 
        {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });

    ////////////////////// GESTION convention
    Route::get('gestionfournisseurs/conventions/listefournisseurs', 'gestionfour\conventionController@listefour')->name('gestionfour.conventions.listefour');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/create{fournisseur}', 'gestionfour\conventionController@createconv')->name('gestionfour.conventions.createconv');
    Route::post('gestionfournisseurs/conventions/getdatacreateconv', 'gestionfour\conventionController@createConventionTable')->name('gestionfour.conventions.createConventionTable');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/listeconventions/{fournisseur}', 'gestionfour\conventionController@listeconvention')->name('gestionfour.conventions.listeconvention');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/storeconv{fournisseur}', 'gestionfour\conventionController@storeConv')->name('gestionfour.conventions.storeConv');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/valider', 'gestionfour\conventionController@done')->name('gestionfour.conventions.done');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/devalider', 'gestionfour\conventionController@undone')->name('gestionfour.conventions.undone');
    Route::delete('gestionfournisseurs/conventions/listefournisseurs/listeconventions/supprimer/{id}', 'gestionfour\conventionController@deleteconv')->name('gestionfour.conventions.deleteconv2');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/listeconvention/show/{id}', 'gestionfour\conventionController@showconv')->name('gestionfour.conventions.showconv');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/listeconvention/print/{id}', 'gestionfour\conventionController@print')->name('gestionfour.conventions.print');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/listeconvention/modifier/{id}', 'gestionfour\conventionController@modifierconvention')->name('gestionfour.conventions.modifierconvention');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/listeconvention/update/{id}', 'gestionfour\conventionController@updateconvention')->name('gestionfour.conventions.updateconvention');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/listeconvention/listefichier/{id}', 'gestionfour\conventionController@listefichier')->name('gestionfour.conventions.listefichier');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/listeconvention/getdataFileUpload', 'gestionfour\conventionController@getdataFileUpload')->name('gestionfour.conventions.getdataFileUpload');
    Route::delete('gestionfournisseurs/conventions/listefournisseurs/listeconventions/supprimerfichier/{id}', 'gestionfour\conventionController@deletefile')->name('gestionfour.conventions.deletefile');
    Route::get('gestionfournisseurs/conventions/listefournisseurs/listeconventions/download/{idconv}/{file}', 'gestionfour\conventionController@downloadfile')->name('gestionfour.conventions.downloadfile');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/listeconventions/upload/{idconv}', 'gestionfour\conventionController@uploadfile')->name('gestionfour.conventions.uploadfile');
    Route::post('gestionfournisseurs/conventions/listefournisseurs/listeconventions/getdatatableconv', 'gestionfour\conventionController@getdatatableconv')->name('gestionfour.conventions.getdatatableconv');

    ////////////////////// FIN GESTION convention

    ////bon sortie

    Route::get('importbonexcel/bonssortiemags', 'BonSortieMagController@importExcelBonssm')->name('bonssortiemags.importExcelBonssm');
    Route::post('importbonexcel/bonssortiemagscharge', 'BonSortieMagController@bonsmimportcharge')->name('bonssortiemags.bonsmimportcharge'); //////preview
    Route::post('importbonexcel/bonssortiemagscreate', 'BonSortieMagController@createbonsmimport')->name('bonssortiemags.createbonsmimport');
    Route::post('importbonexcel/bonssortiemagsstore', 'BonSortieMagController@storebonsortieimport')->name('bonssortiemags.storebonsortieimport');


});

Route::group(['middleware' => ['auth']], function () 
{

    Route::get('fileproduit/{filename}', function ($filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/imageproduit/' . $filename);
        if (!File::exists($path)) {
            dd($path);
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });


    Route::get('filebv/{filename}/{idbon}', function ($filename, $idbon) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/imagebv/bonentree/' . $idbon . '/' . $filename);
        if (!File::exists($path)) {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });


Route::group(['middleware' => ['auth', 'role:Sup']], function () {

    Route::get('superviseur/fournisseurs/excel', 'superviseur\FournisseurController@EXCELfour')->name('superviseur.fournisseur.EXCELfour'); //////excel four
    Route::get('superviseur/bons/createbonsite', 'superviseur\BonController@createbonsite')->name('superviseur.bons.createbonsite');
    Route::post('superviseur/bons/storebonsite', 'superviseur\BonController@storebonsite')->name('superviseur.bons.storebonsite');
    Route::post('superviseur/bons/{idbon}', 'superviseur\BonController@updatebon')->name('superviseur.bons.updatebon');
    Route::get('superviseur/editbons/{bon}', 'superviseur\BonController@edit')->name('superviseur.bons.edit'); ///////
    Route::post('superviseur/bons/prod/{idbon}', 'superviseur\BonController@updatebonprod')->name('superviseur.bons.updatebonprod'); //////////////////////
    Route::get('superviseur/bons/listebons', 'superviseur\BonController@indexbons')->name('superviseur.bons.listebons');
    Route::get('superviseur/bons/{bon}', 'superviseur\BonController@show')->name('superviseur.bons.show'); ////////
    Route::delete('superviseur/bons/{bon}', 'superviseur\BonController@destroy')->name('superviseur.bons.destroy'); ///////
    Route::get('superviseur/bons/EXCELbon/{idbon}', 'superviseur\BonController@EXCELbon')->name('superviseur.bons.EXCELbon');
    Route::get('superviseur/printbonen/{idbon}', 'superviseur\BonController@Printbonen')->name('superviseur.bons.Printbonen'); ////print
    Route::post('postdataproductsup', 'superviseur\BonController@postdataproductsup')->name('superviseur.bons.postdataproductsup');
    Route::post('getdataBonsSup', 'superviseur\BonController@getdataBonsSup')->name('superviseur.bons.getdataBonsSup');
    Route::post('getdataEditBonSup', 'superviseur\BonController@getdataEditBonSup')->name('superviseur.bons.getdataEditBonSup');
    Route::post('superviseur/bons/listebons/validerbe', 'superviseur\BonController@validerbe')->name('superviseur.bons.validerbe');
    Route::get('superviseur/bons/photobl/{idbon}', 'superviseur\BonController@photobonl')->name('superviseur.bons.photobonl'); ////////////////////////////////////////////////////////
    Route::post('superviseur/bons/uploadphotobl/{idbon}', 'superviseur\BonController@uploadphotobonl')->name('superviseur.bons.uploadphotobonl');
    Route::delete('superviseur/bons/deletephotobl/{filename}/{idbon}', 'superviseur\BonController@deletephotobonl')->name('superviseur.bons.deletephotobonl');
    Route::post('superviseur/bons/updatenumbonl/{idbon}', 'superviseur\BonController@numbonl')->name('superviseur.bons.numbonl'); /////////////

    Route::get('superviseur/showmonsite', 'superviseur\SiteController@showMonsite')->name('superviseur.site.showMonsite');

    Route::post('getdataSitesup', 'superviseur\SiteController@getdataSitesup')->name('superviseur.sites.getdataSitesup');



    

    Route::get('superviseur/prgs/edit2/{idSite}/{date}', 'superviseur\prgController@edit2')->name('superviseur.prgs.edit2');

    Route::get('superviseur/prgs/createSite/{idSite}', 'superviseur\prgController@createSite')->name('superviseur.prgs.createSite');

    Route::get('superviseur/prgs/showprg/{idSite}', 'superviseur\prgController@showprg')->name('superviseur.prgs.showprg');



    ////bon sortie importer

    Route::get('superviseur/importbonexcel/bonssortiemags', 'superviseur\BonSortieMagController@importExcelBonssm')->name('superviseur.bonssortiemags.importExcelBonssm');
    Route::post('superviseur/importbonexcel/bonssortiemagscharge', 'superviseur\BonSortieMagController@bonsmimportcharge')->name('superviseur.bonssortiemags.bonsmimportcharge'); //////preview
    Route::post('superviseur/importbonexcel/bonssortiemagscreate', 'superviseur\BonSortieMagController@createbonsmimport')->name('superviseur.bonssortiemags.createbonsmimport');
    Route::post('superviseur/importbonexcel/bonssortiemagsstore', 'superviseur\BonSortieMagController@storebonsortieimport')->name('superviseur.bonssortiemags.storebonsortieimport');

    //////// bon de com
    Route::get('superviseur/bonCommandes/createCommande', 'superviseur\commandeControllerSup@createCommande')->name('superviseur.bonCommandes.createCommande');
    Route::get('superviseur/bonCommandes', 'superviseur\commandeControllerSup@index')->name('superviseur.bonCommandes.indexSup');
    Route::post('superviseur/createCommandeTable', 'superviseur\commandeControllerSup@createCommandeTable')->name('superviseur.bonCommandes.createCommandeTable');
    Route::post('superviseur/bonCommandes/storeCommande', 'superviseur\commandeControllerSup@storeCommande')->name('superviseur.bonCommandes.storeCommande');
    Route::get('superviseur/bonCommandes/listeCommandes', 'superviseur\commandeControllerSup@listeCommandes')->name('superviseur.bonCommandes.listeCommandes2');

    ///// fin de bon de com



    ////////////////////////////////   FICHE STOCK
    Route::post('superviseur/fichestock/datatableproduct', 'superviseur\FicheStock@datatableproduct')->name('superviseur.fichestock.datatableproduct');
    Route::get('superviseur/fichestock/create', 'superviseur\FicheStock@index')->name('superviseur.fichestock.index');
    Route::get('superviseur/fichestock', 'superviseur\FicheStock@accueil')->name('superviseur.fichestock.accueil');
    Route::post('superviseur/fichestock/recherche', 'superviseur\FicheStock@resultat')->name('superviseur.fichestock.resultat');
    Route::get('superviseur/fichestock/recherche/EXCELfiche/{numprod}/{date}/{nbr}', 'superviseur\FicheStock@EXCELfiche')->name('superviseur.fichestock.EXCELfiche');

    //////////
    //Route::get('superviseur/fichestock/indexminmaxsite','FicheStock@indexsite')->name('fichestock.indexsite');
    Route::get('superviseur/fichestock/indexminmaxsite', 'superviseur\FicheStock@indexminmax')->name('superviseur.fichestock.indexminmax');
    Route::get('superviseur/fichestock/indexminmaxsite/recherche', 'superviseur\FicheStock@searchminmax')->name('superviseur.fichestock.searchminmax');
    Route::post('superviseur/fichestock/indexminmaxsite/storeminmax', 'superviseur\FicheStock@storeminmax')->name('superviseur.fichestock.storeminmax');
    Route::get('superviseur/fichestock/datatableproductrefresh', 'superviseur\FicheStock@datatableproductRefresh')->name('superviseur.fichestock.datatableproductRefresh');

    /////////////////////////////////// FIN FICHE STOCK


    Route::get('fileuser/{filename}', function ($filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/imageuser/' . $filename);
        if (!File::exists($path)) {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });



    Route::get('filelogosup/{filename}', function ($filename) 
    {
        // Add folder path here instead of storing in the database.
        $path = storage_path('app/public/logo/' . $filename);
        if (!File::exists($path)) {

            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    });
});
