<?php

namespace app\modules\report\controllers;

use yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;

use app\modules\report\helpers\PresentationHelper;

use app\modules\report\models\Model;
    use app\modules\report\models\Presentation;
    use app\modules\report\models\Section;
    use app\modules\report\models\SectionType;
use app\modules\report\models\Page;
use app\modules\report\models\PageElement;
use app\modules\report\models\DinamicForm;
use app\modules\report\models\SacForm;
use app\modules\report\models\PresentationSearch;
use app\modules\report\models\SheetForm;

use vova07\console\ConsoleRunner;

/**
 * Default controller for the `report` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $client = new \Google_Client();
        $client->setApplicationName('report-lg-montana-studio');
        $scopes = \app\modules\report\helpers\PresentationHelper::getScope();
        $client->setScopes($scopes);
        $pathCredentials = \Yii::getAlias('@app/modules/report/credentials/credentials.json');
        $client->setAuthConfig($pathCredentials);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $userId = \Yii::$app->user->id;
        $tokenPath = \Yii::getAlias("@app/modules/report/credentials/{$userId}.json");
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        
        if(Yii::$app->request->get('code')){

            $authCode = Yii::$app->request->get('code');
            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            return $this->render('index');
            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                return $this->redirect($authUrl);
                
            }
            
        }

        $searchModel = new PresentationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }
    /**
     * Renders the sheet view for the module
     * @return string
     */
    public function actionUrlSheet(){
        $sheetForm = new SheetForm();
        if ($sheetForm->load(Yii::$app->request->post()) && $sheetForm->validate()) {
            //change to user loged
            $userId = \Yii::$app->user->id;
            $session = Yii::$app->session;
            
            $data = $sheetForm->url;
            $session->set("url-sheet-{$userId}", $data);
            // render to form create 
            $this->redirect(['create']);
        }
        return $this->render('sheet',['sheetForm' => $sheetForm]);
    }


    /**
     * Displays a single Presentation model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $pathConsole = Yii::getAlias('@app')."/yii";
        $cr = new ConsoleRunner(['file' => $pathConsole]);
        $cr->run("presentation/index {$model->id}");

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


    /**
     * Creates a new Presentation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Presentation();
        $modelsSection = [new Section];
        $modelsPage = [[new Page]];

        $modelOverViewSection = new Section();
        $modelOverViewPage = new Page();
        $modelOverViewPageElement = [new PageElement,new PageElement,new PageElement];

        $modelsSectionDinamic = [new DinamicForm];

        $sacForm = new SacForm();

        // get url form session
        $session = Yii::$app->session;
        // get user logged
        $userId = \Yii::$app->user->id;
        $url = $session->get("url-sheet-{$userId}");
        $sheetRanges = \app\modules\report\helpers\PresentationHelper::getSheetNames($url);
        if(empty($sheetRanges)){
            return $this->redirect(['url-sheet']);
        }
        

        if ($model->load(Yii::$app->request->post())) {

            $modelsSection = Model::createMultiple(Section::classname());

            $request = Yii::$app->request->post();
           
            $typeFixedSectionRequest = ArrayHelper::remove($request['Section'] ,'fixed');
            $typeFixedPageRequest = ArrayHelper::remove($request['Page'],'fixed');

            $dinamicRequest = ArrayHelper::remove($request ,'DinamicForm');

            $sacRequest = ArrayHelper::remove($request ,'SacForm');
            
            Model::loadMultiple($modelsSection, $request);

            // validate presentation and section models
            $model->date =  \Yii::$app->formatter->asTimestamp('now', 'php:U'); 
            //change user
            $model->userId = 1;
            $model->url_sheet = $url;
            
            $session->remove("url-sheet-{$model->userId}");
            
            $valid = $model->validate();
            $valid = Model::validateMultiple($modelsSection) && $valid;
            

            

            if (isset($request['Page'][0][0])) {
                foreach ($request['Page'] as $indexSection => $pages) {
                    foreach ($pages as $indexPage => $page) {
                        $data['Page'] = $page;
                        $modelPage = new Page;
                        $modelPage->load($data);
                        $modelsPage[$indexSection][$indexPage] = $modelPage;
                        $valid = $modelPage->validate();
                        
                    }
                }
            }
             
            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $model->save(false)) {
                        foreach ($modelsSection as $indexSection => $modelSection) {

                            if ($flag === false) {
                                break;
                            }
                            $modelSection->presentationId = $model->id;
                            $typeSection = SectionType::find()->where(['name' => 'static'])->one();
                            $modelSection->typeSection = $typeSection->id;

                            if($modelSection->validate()){
                                if (!($flag = $modelSection->save(false))) {
                                    break;
                                }
                            }

                            if (isset($modelsPage[$indexSection]) && is_array($modelsPage[$indexSection])) {
                                foreach ($modelsPage[$indexSection] as $indexPage => $modelPage) {
                                    $modelPage->sectionId = $modelSection->id;
                                    if($modelPage->validate()){
                                        if (!($flag = $modelPage->save(false))) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        // save fixed
                        $typeSection = SectionType::find()->where(['name' => 'fixed'])->one();
                        $modelSection = PresentationHelper::saveSection($typeFixedSectionRequest,$typeSection->id,$model->id);
                        
                        if($modelSection->validate()){
                            $modelSection->save();
                            $modelPageFixed = new Page;
                            $modelPageFixed->sectionId = $modelSection->id;
                            $modelPageFixed->title = $typeFixedPageRequest['title'];
                            
                            if($modelPageFixed->validate()){
                                $modelPageFixed->save();
                                // save page element
                                $pageElements = $request['PageElement'];
                                PresentationHelper::savePageElements($pageElements,$modelPageFixed->id);

                            }else{
                                print_r($modelPageFixed->errors);
                                die(); 
                            }
                        }else{
                            print_r($modelSection->errors);
                            die();
                        }
                        
                        foreach($dinamicRequest as $indexDinamic => $dinamic){
                            //save dinamic
                            $typeSection = SectionType::find()->where(['name' => 'dinamic'])->one();
                            $modelSection = PresentationHelper::saveSection($dinamic,$typeSection->id,$model->id);

                            if($modelSection->validate()){
                                $modelSection->save();
                                $modelPageDinamic = PresentationHelper::savePage($dinamic['title'],$modelSection->id);
                                
                                

                                if($modelPageDinamic->validate()){
                                    $modelPageDinamic->save();
                                    
                                    // save page element
                                    if($dinamic['graphic_facebook'] != '' && $dinamic['graphic_instagram'] != ''){
                                        PresentationHelper::saveDinamicPageElement('alt_graph_facebook',$dinamic['graphic_facebook'],$modelPageDinamic->id);
                                        PresentationHelper::saveDinamicPageElement('alt_graph_instagram',$dinamic['graphic_instagram'],$modelPageDinamic->id);
                                        
                                    }
    
                                    if($dinamic['table_facebook'] != '' && $dinamic['table_instagram'] != ''){
                                        $pageTable = PresentationHelper::savePage($modelPageDinamic->title,$modelSection->id);
                                        if($pageTable->validate()){
                                            $pageTable->save();
                                            PresentationHelper::saveDinamicPageElement('alt_table_facebook',$dinamic['table_facebook'],$pageTable->id);
                                            PresentationHelper::saveDinamicPageElement('alt_table_instagram',$dinamic['table_instagram'],$pageTable->id);
                                        }
                                        
                                    }

                                    // save best_publication by default
                                    $title = DinamicForm::TITLE_FIXED['best_publication'];
                                    $pageBestPublication = PresentationHelper::savePage($title,$modelSection->id);
                                    if($pageBestPublication->validate()){
                                        $pageBestPublication->save();
                                    }

                                    if($dinamic['sentiment_by_category_checked']){

                                        if(isset($dinamic['sentiment_by_category']) && !empty($dinamic['sentiment_by_category'])){
                                            $title = $dinamic['sentiment_by_category'];
                                        }else{
                                            $title = DinamicForm::TITLE_FIXED['sentiment_by_category'];
                                        }
                                        $pageSentimentByCategorie = PresentationHelper::savePage($title,$modelSection->id);
                                        if($pageSentimentByCategorie->validate()){
                                            $pageSentimentByCategorie->save();
                                        }
                                    }

                                    if($dinamic['message_by_product_category_checked']){

                                        if(isset($dinamic['message_by_product_category']) && !empty($dinamic['message_by_product_category'])){
                                            $title = $dinamic['message_by_product_category'];
                                        }else{
                                            $title = DinamicForm::TITLE_FIXED['message_by_product_category'];
                                        }
                                        $pageMessageByProduct = PresentationHelper::savePage($title,$modelSection->id);
                                        if($pageMessageByProduct->validate()){
                                            $pageMessageByProduct->save();
                                        }
                                    }

                                    if($dinamic['case_succes_sac_checked']){

                                        if(isset($dinamic['case_succes_sac']) && !empty($dinamic['case_succes_sac'])){
                                            $title = $dinamic['case_succes_sac'];
                                        }else{
                                            $title = DinamicForm::TITLE_FIXED['case_succes_sac'];
                                        }
                                        $pageCaseSuccesSAC = PresentationHelper::savePage($title,$modelSection->id);
                                        if($pageCaseSuccesSAC->validate()){
                                            $pageCaseSuccesSAC->save();
                                        }
                                    }

                                }else{
                                    print_r($modelPageDinamic->errors);
                                    die(); 
                                }

                            }else{
                                print_r($modelSection->errors);
                                die();
                            }

                        }
                        
                        // save sac
                        $typeSection = SectionType::find()->where(['name' => 'sac'])->one();
                        $modelSection = PresentationHelper::saveSection($sacRequest,$typeSection->id,$model->id);
                        if($modelSection->validate()){
                            $modelSection->save();
                            // saves pages & pageElement
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][0],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_table_inbox_sac',$sacRequest['tables_coordinates'][0],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_table_checked_sac',$sacRequest['tables_coordinates'][1],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_table_answered_sac',$sacRequest['tables_coordinates'][2],$modelPageDinamic->id);
                            }
                            // mencion_by_categories
                            if($sacRequest['mencion_by_categories_checked']){

                                if(isset($sacRequest['mencion_by_categories']) && !empty($sacRequest['mencion_by_categories'])){
                                    $title = $sacRequest['mencion_by_categories'];
                                }else{
                                    $title = 'MENCIONES POR CATEGORÍA';
                                }
                                
                                $modelPageDinamic = PresentationHelper::savePage($title,$modelSection->id);
                                $modelPageDinamic->save();
                            }
                            // MENSAJES RECIBIDOS POR RED SOCIAL
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][1],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_table_received_sac',$sacRequest['tables_coordinates'][3],$modelPageDinamic->id);
                            }
                            // comentarios publicos
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][2],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_public_month_from_sac',$sacRequest['graph_coordinates'][0],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_public_month_to_sac',$sacRequest['graph_coordinates'][1],$modelPageDinamic->id);
                            }
                            // comentarios privados
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][3],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_private_month_from_sac',$sacRequest['graph_coordinates'][2],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_private_month_to_sac',$sacRequest['graph_coordinates'][3],$modelPageDinamic->id);
                            }

                            // CLASIFICACIÓN DEL TIPO DE MENSAJE
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][4],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_table_type_messages_sac',$sacRequest['tables_coordinates'][4],$modelPageDinamic->id);
                            }
                        }

                    }
                   
                    if ($flag) {
                        $transaction->commit();
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        
                        $transaction->rollBack();
                    }
                } catch (Exception $e) {
                   
                    $transaction->rollBack();
                }
            }

        }

        return $this->render('create', [
            'model' => $model,
            'modelsSection' => $modelsSection,
            'modelsPage' => $modelsPage,
            'modelOverViewSection' => $modelOverViewSection,
            'modelOverViewPage' => $modelOverViewPage,
            'modelOverViewPageElement' => $modelOverViewPageElement,
            
            'modelsSectionDinamic' => $modelsSectionDinamic,
            'sacForm' => $sacForm,
            'sheetRanges' => $sheetRanges
            
        ]);
    }

      /**
     * Updates an existing Presentation model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $sheetRanges = PresentationHelper::getSheetNames($model->url_sheet);
        
        $typeSection = SectionType::find()->where(['name' => 'static'])->one();
        $modelsSection = $model->getSections()->where(['typeSection' => $typeSection->id])->all();

        $modelsPage = [];
        $oldPages = [];

        if (!empty($modelsSection)) {
            foreach ($modelsSection as $indexSection => $modelSection) {
                if($modelSection->pages){
                    $pages = $modelSection->pages;
                    $modelsPage[$indexSection] = $pages;
                    $oldPages = ArrayHelper::merge(ArrayHelper::index($pages, 'id'), $oldPages);
                }
            }
        }
        // overview
        $typeSection = SectionType::find()->where(['name' => 'fixed'])->one();
        $modelOverViewSection = $model->getSections()->where(['typeSection' => $typeSection->id])->one();
        $modelOverViewPage = $modelOverViewSection->getPages()->where(['sectionId' => $modelOverViewSection->id])->one();
        $modelOverViewPageElement = $modelOverViewPage->pageElements;

        // dinamic categories
        $typeSection = SectionType::find()->where(['name' => 'dinamic'])->one();
        $modelCategoriesSection = $model->getSections()->where(['typeSection' => $typeSection->id])->all();
        

        $modelsSectionDinamic = [];

        if (!empty($modelCategoriesSection)) {
            foreach ($modelCategoriesSection as $indexCategoriesSection => $modelCategorieSection) {
                
                $dinamicModel = new DinamicForm();
                $dinamicModel->head_title = $modelCategorieSection->head_title;
                if($modelCategorieSection->pages){
                    $pages = $modelCategorieSection->pages;
                    $dinamicModel->title = $pages[0]->title;

                    $pageElementsGraphFacebook = $pages[0]->getPageElements()->where(['name' => 'alt_graph_facebook'])->one();
                    $dinamicModel->graphic_facebook = $pageElementsGraphFacebook->value;

                    $pageElementsGraphInstagram = $pages[0]->getPageElements()->where(['name' => 'alt_graph_instagram'])->one();
                    $dinamicModel->graphic_instagram = $pageElementsGraphInstagram->value;

                    $pageElementsTableFacebook = $pages[1]->getPageElements()->where(['name' => 'alt_table_facebook'])->one();
                    $dinamicModel->table_facebook = (isset($pageElementsTableFacebook->value)) ? $pageElementsTableFacebook->value : '';

                    $pageElementsTableInstagram = $pages[1]->getPageElements()->where(['name' => 'alt_table_instagram'])->one();
                    $dinamicModel->table_instagram = (isset($pageElementsTableInstagram->value)) ? $pageElementsTableInstagram->value : '';

                    if(isset($pages[3])){
                        $dinamicModel->sentiment_by_category_checked = 1;
                        $dinamicModel->sentiment_by_category = $pages[3]->title;
                    }

                    if(isset($pages[4])){
                        $dinamicModel->message_by_product_category_checked = 1;
                        $dinamicModel->message_by_product_category = $pages[4]->title;
                    }

                    if(isset($pages[5])){
                        $dinamicModel->case_succes_sac_checked = 1;
                        $dinamicModel->case_succes_sac = $pages[5]->title;
                    }

                    $modelsSectionDinamic[$indexCategoriesSection] = $dinamicModel;
                    
                }
                
            }
        }
        
        $sacForm = new SacForm();
        $typeSection = SectionType::find()->where(['name' => 'sac'])->one();
        $modelSacSections = $model->getSections()->where(['typeSection' => $typeSection->id])->all();

        if(!empty($modelSacSections)){
            foreach ($modelSacSections as $indexSacSection => $modelSacSection) {
                $sacForm->head_title = $modelSacSection->head_title;
                if($modelSacSection->pages){
                    
                    $pages = $modelSacSection->pages;
                    $sacForm->titles[0] = $pages[0]->title;
                    
                    if($pages[0]->pageElements){
                        foreach($pages[0]->pageElements as $index => $pageElement) {
                            $sacForm->tables_coordinates[$index] = $pageElement->value;
                        }
                    }

                    // MENCIONES POR CATEGORÍA
                    if(isset($pages[1]) && empty($pages[1]->pageElements)){
                        $sacForm->mencion_by_categories_checked = 1;
                        $sacForm->mencion_by_categories = $pages[1]->title;
                    }
                    $index = empty($pages[1]->pageElements) ? 2 : 1;

                    // Slide Mensajes recibidos por red Social
                    if(isset($pages[$index])){
                        $sacForm->titles[1] = $pages[$index]->title;
                        $pageElement = $pages[$index]->getPageElements()->where(['name' => 'alt_table_received_sac'])->one();
                        $sacForm->tables_coordinates[3] = (isset($pageElement->value)) ? $pageElement->value : '';
                    }
                    // Slide Comentarios Positivos
                    $index++;
                    if(isset($pages[$index])){
                        $graph_from = $pages[$index]->getPageElements()->where(['name' => 'alt_graph_coments_public_month_from_sac'])->one();
                        $graph_to = $pages[$index]->getPageElements()->where(['name' => 'alt_graph_coments_public_month_to_sac'])->one();
                        
                        if(!empty($graph_from) && !empty($graph_to)){
                            $sacForm->titles[2] =  $pages[$index]->title;
                            $sacForm->graph_coordinates[0] = $graph_from->value;
                            $sacForm->graph_coordinates[1] = $graph_to->value;
                        }
                    }
                    // Slide Comentarios Privados
                    $index++;
                    if(isset($pages[$index])){
                        $graph_from = $pages[$index]->getPageElements()->where(['name' => 'alt_graph_coments_private_month_from_sac'])->one();
                        $graph_to = $pages[$index]->getPageElements()->where(['name' => 'alt_graph_coments_private_month_to_sac'])->one();
                        
                        if(!empty($graph_from) && !empty($graph_to)){
                            $sacForm->titles[3] =  $pages[$index]->title;
                            $sacForm->graph_coordinates[2] = $graph_from->value;
                            $sacForm->graph_coordinates[3] = $graph_to->value;
                        }
                    }
                    // Slide Clasificacion tipos de Mensajes
                    $index++;
                    if(isset($pages[$index])){
                        $table_message = $pages[$index]->getPageElements()->where(['name' => 'alt_table_type_messages_sac'])->one();
                        if(!empty($table_message)){
                            $sacForm->titles[4] =  $pages[$index]->title;
                            $sacForm->tables_coordinates[4] =  $table_message->value;
                        } 
                    }
                }
            }
        }
       

        
        if ($model->load(Yii::$app->request->post())) {
            // reset
            $modelsPage = [];

            $request = Yii::$app->request->post();
           
            $typeFixedSectionRequest = ArrayHelper::remove($request['Section'] ,'fixed');
            $typeFixedPageRequest = ArrayHelper::remove($request['Page'],'fixed');

            $oldSectionIDs = ArrayHelper::map($modelsSection, 'id', 'id');
            $modelsSection = Model::createMultiple(Section::classname(), $modelsSection);
            Model::loadMultiple($modelsSection, $request);
            $deletedSectionIDs = array_diff($oldSectionIDs, array_filter(ArrayHelper::map($modelsSection, 'id', 'id')));


            // validate person and houses models
            $model->date =  \Yii::$app->formatter->asTimestamp('now', 'php:U'); 
            $model->userId =  1; 
            $valid = $model->validate();
            $valid = Model::validateMultiple($modelsSection) && $valid;

            $pageIDs = [];
            if (isset($request['Page'][0][0])) {
                foreach ($request['Page'] as $indexSection => $pages) {
                    $pageIDs = ArrayHelper::merge($pageIDs, array_filter(ArrayHelper::getColumn($pages, 'id')));
                    
                    foreach ($pages as $indexPage => $page) {
                        $data['Page'] = $page;
                        $modelPage = (isset($page['id']) && isset($oldPages[$page['id']])) ? $oldPages[$page['id']] : new Page;
                        $modelPage->load($data);
                        $modelsPage[$indexSection][$indexPage] = $modelPage;
                        $valid = $modelPage->validate();
                    }
                }
            }
            
            $oldPagesIDs = ArrayHelper::getColumn($oldPages, 'id');
            $deletedPagesIDs = array_diff($oldPagesIDs, $pageIDs);

            //CATEGORY FORM
            $dinamicRequest = ArrayHelper::remove($request ,'DinamicForm');
            $oldIDs = ArrayHelper::map($modelCategoriesSection, 'id', 'id');
            $modelsCategories = Model::createMultiple(Section::classname(), $modelCategoriesSection);
            $modelsTest = Model::loadMultiple($modelsCategories, $dinamicRequest );
            $deletedCategoriesSectionIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelsCategories, 'id', 'id')));

            $sacRequest = ArrayHelper::remove($request ,'SacForm');
            $typeSection = SectionType::find()->where(['name' => 'sac'])->one();
            $deleteSacId = Section::find()->where(['presentationId' => $model->id,'typeSection' => $typeSection->id])->one();

            
            if ($valid) {
                
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $model->save(false)) {

                        if (! empty($deletedPagesIDs)) {
                            Page::deleteAll(['id' => $deletedPagesIDs]);
                        }

                        if (! empty($deletedSectionIDs)) {
                            Section::deleteAll(['id' => $deletedSectionIDs]);
                        }

                        if (! empty($deletedCategoriesSectionIDs)) {
                            Section::deleteAll(['id' => $deletedCategoriesSectionIDs]);
                        }

                        if(! empty($deleteSacId)){
                            Section::deleteAll(['id' => $deleteSacId->id]);
                        }
                       
                        foreach ($modelsSection as $indexSection => $modelSection) {

                            if ($flag === false) {
                                break;
                            }

                            $modelSection->presentationId = $model->id;
                            $typeSection = SectionType::find()->where(['name' => 'static'])->one();
                            $modelSection->typeSection = $typeSection->id;

                            if($modelSection->validate()){
                                if (!($flag = $modelSection->save(false))) {
                                    break;
                                }
                            }


                            if (isset($modelsPage[$indexSection]) && is_array($modelsPage[$indexSection])) {
                                foreach ($modelsPage[$indexSection] as $indexPage => $modelPage) {
                                    $modelPage->sectionId = $modelSection->id;
                                    if($modelPage->validate()){
                                        if (!($flag = $modelPage->save(false))) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        // delete fixed overview
                        Section::deleteAll(['id' => $modelOverViewSection->id]);
                        // save fixed
                        $typeSection = SectionType::find()->where(['name' => 'fixed'])->one();
                        $modelSectionFixed = new Section;
                        $modelSectionFixed->presentationId = $model->id;
                        $modelSectionFixed->typeSection = $typeSection->id;
                        $modelSectionFixed->head_title = $typeFixedSectionRequest['head_title'];
                        
                        
                        if($modelSectionFixed->validate()){
                            $modelSectionFixed->save();
                            // delete fixed
                            Page::deleteAll(['id' => $modelOverViewPage->id]);
                            // save page
                            $modelPageFixed = new Page;
                            $modelPageFixed->sectionId = $modelSectionFixed->id;
                            $modelPageFixed->title = $typeFixedPageRequest['title'];
                            
                            if($modelPageFixed->validate()){
                                $modelPageFixed->save();
                                // save page element
                                $pageElements = $request['PageElement'];
                                PresentationHelper::savePageElements($pageElements,$modelPageFixed->id);
                            }else{
                                print_r($modelPageFixed->errors);
                                die(); 
                            }
                           
                            $brandName = null;
                            foreach($dinamicRequest as $indexDinamic => $dinamic){
                                //save dinamic
                                $typeSection = SectionType::find()->where(['name' => 'dinamic'])->one();
                                $modelSection = PresentationHelper::saveSection($dinamic,$typeSection->id,$model->id);
    
                                if($modelSection->validate()){
                                    $modelSection->save();
                                    $modelPageDinamic = PresentationHelper::savePage($dinamic['title'],$modelSection->id);
                                    
    
                                    if($modelPageDinamic->validate()){
                                        $modelPageDinamic->save();
                                        
                                        // save page element
                                        if($dinamic['graphic_facebook'] != '' && $dinamic['graphic_instagram'] != ''){
                                            PresentationHelper::saveDinamicPageElement('alt_graph_facebook',$dinamic['graphic_facebook'],$modelPageDinamic->id);
                                            PresentationHelper::saveDinamicPageElement('alt_graph_instagram',$dinamic['graphic_instagram'],$modelPageDinamic->id);
                                            
                                        }
        
                                        if($dinamic['table_facebook'] != '' && $dinamic['table_instagram'] != ''){
                                            $pageTable = PresentationHelper::savePage($modelPageDinamic->title,$modelSection->id);
                                            if($pageTable->validate()){
                                                $pageTable->save();
                                                PresentationHelper::saveDinamicPageElement('alt_table_facebook',$dinamic['table_facebook'],$pageTable->id);
                                                PresentationHelper::saveDinamicPageElement('alt_table_instagram',$dinamic['table_instagram'],$pageTable->id);
                                            }
                                            
                                        }
                                        // save best_publication by default
                                        $title = DinamicForm::TITLE_FIXED['best_publication'];
                                        $pageBestPublication = PresentationHelper::savePage($title,$modelSection->id);
                                        if($pageBestPublication->validate()){
                                            $pageBestPublication->save();
                                        }
    
                                        if($dinamic['sentiment_by_category_checked']){
    
                                            if(isset($dinamic['sentiment_by_category']) && !empty($dinamic['sentiment_by_category'])){
                                                $title = $dinamic['sentiment_by_category'];
                                            }else{
                                                $title = DinamicForm::TITLE_FIXED['sentiment_by_category'];
                                            }
                                            $pageSentimentByCategorie = PresentationHelper::savePage($title,$modelSection->id);
                                            if($pageSentimentByCategorie->validate()){
                                                $pageSentimentByCategorie->save();
                                            }
                                        }
    
                                        if($dinamic['message_by_product_category_checked']){
    
                                            if(isset($dinamic['message_by_product_category']) && !empty($dinamic['message_by_product_category'])){
                                                $title = $dinamic['message_by_product_category'];
                                            }else{
                                                $title = DinamicForm::TITLE_FIXED['message_by_product_category'];
                                            }
                                            $pageMessageByProduct = PresentationHelper::savePage($title,$modelSection->id);
                                            if($pageMessageByProduct->validate()){
                                                $pageMessageByProduct->save();
                                            }
                                        }
    
                                        if($dinamic['case_succes_sac_checked']){
    
                                            if(isset($dinamic['case_succes_sac']) && !empty($dinamic['case_succes_sac'])){
                                                $title = $dinamic['case_succes_sac'];
                                            }else{
                                                $title = DinamicForm::TITLE_FIXED['case_succes_sac'];
                                            }
                                            $pageCaseSuccesSAC = PresentationHelper::savePage($title,$modelSection->id);
                                            if($pageCaseSuccesSAC->validate()){
                                                $pageCaseSuccesSAC->save();
                                            }
                                        }
    
                                    }else{
                                        print_r($modelPageDinamic->errors);
                                        die(); 
                                    }
    
                                }else{
                                    print_r($modelSection->errors);
                                    die();
                                }
    
                            }
                           

                        }else{
                            print_r($modelSectionFixed->errors);
                            die();
                        }

                        // save sac
                        $typeSection = SectionType::find()->where(['name' => 'sac'])->one();
                        $modelSection = PresentationHelper::saveSection($sacRequest,$typeSection->id,$model->id);
                        if($modelSection->validate()){
                            $modelSection->save();
                            // saves pages & pageElement
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][0],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_table_inbox_sac',$sacRequest['tables_coordinates'][0],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_table_checked_sac',$sacRequest['tables_coordinates'][1],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_table_answered_sac',$sacRequest['tables_coordinates'][2],$modelPageDinamic->id);
                            }
                            // mencion_by_categories
                            if($sacRequest['mencion_by_categories_checked']){

                                if(isset($sacRequest['mencion_by_categories']) && !empty($sacRequest['mencion_by_categories'])){
                                    $title = $sacRequest['mencion_by_categories'];
                                }else{
                                    $title = 'MENCIONES POR CATEGORÍA';
                                }
                                
                                $modelPageDinamic = PresentationHelper::savePage($title,$modelSection->id);
                                $modelPageDinamic->save();
                            }
                            // MENSAJES RECIBIDOS POR RED SOCIAL
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][1],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_table_received_sac',$sacRequest['tables_coordinates'][3],$modelPageDinamic->id);
                            }
                            // comentarios publicos
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][2],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_public_month_from_sac',$sacRequest['graph_coordinates'][0],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_public_month_to_sac',$sacRequest['graph_coordinates'][1],$modelPageDinamic->id);
                            }
                            // comentarios privados
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][3],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_private_month_from_sac',$sacRequest['graph_coordinates'][2],$modelPageDinamic->id);
                                PresentationHelper::saveDinamicPageElement('alt_graph_coments_private_month_to_sac',$sacRequest['graph_coordinates'][3],$modelPageDinamic->id);
                            }

                            // CLASIFICACIÓN DEL TIPO DE MENSAJE
                            $modelPageDinamic = PresentationHelper::savePage($sacRequest['titles'][4],$modelSection->id);
                            if($modelPageDinamic->save()){
                                PresentationHelper::saveDinamicPageElement('alt_table_type_messages_sac',$sacRequest['tables_coordinates'][4],$modelPageDinamic->id);
                            }
                        }


                    }

                    if ($flag) {
                        $transaction->commit();
                        // restore url presentation
                        $model->url_presentation = null;
                        $model->status = 1;
                        $model->save();
                        return $this->redirect(['view', 'id' => $model->id]);
                    } else {
                        $transaction->rollBack();
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }

        }

        return $this->render('update', [
            'model' => $model,
            'modelsSection' => (empty($modelsSection)) ? [new Section] : $modelsSection,
            'modelsPage' => (empty($modelsPage)) ? [[new Page]] : $modelsPage,
            'modelOverViewSection' => $modelOverViewSection,
            'modelOverViewPage' => $modelOverViewPage,
            'modelOverViewPageElement' => $modelOverViewPageElement,
            'modelsSectionDinamic' => (empty($modelsSectionDinamic)) ? [new DinamicForm] : $modelsSectionDinamic,
            'sacForm' => $sacForm,
            'sheetRanges' => $sheetRanges
        ]);
    }

    /**
     * Deletes an existing Presentation model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Presentation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Presentation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Presentation::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
