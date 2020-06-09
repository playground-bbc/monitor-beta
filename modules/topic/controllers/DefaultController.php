<?php

namespace app\modules\topic\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

use app\modules\topic\models\MTopics;
use app\modules\topic\models\MTopicsSearch;

/**
 * Default controller for the `topic` module
 */
class DefaultController extends Controller
{

	/**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'view'],
                'rules' => [
                    [
                        // 'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['POST'],
                    'index' => ['GET', 'POST'],
                ],
            ],
            [
                'class' => 'yii\filters\PageCache',
                'only' => ['index'],
                'duration' => 60,
                'variations' => [
                    \Yii::$app->language,
                ],
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT * FROM m_topics WHERE userId='.Yii::$app->user->getId(),
                ],
            ],
        ];
    }

    /**
     * Displays a single MTopics model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    
    /**
     * Lists all MTopics models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MTopicsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new MTopics model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MTopics();
        $drive   = new \app\models\api\DriveApi();

        if ($model->load(Yii::$app->request->post())) {

            //save end date unix
            $model->end_date = Yii::$app->formatter->asTimestamp($model->end_date);
            $model->userId = Yii::$app->user->getId();
            // save topic
            $model->save();
            // save resourceIds
            $resourcesId = Yii::$app->request->post('MTopics')['resourceId'];
            if ($resourcesId) {
                \app\helpers\TopicsHelper::saveOrUpdateResourceId($resourcesId,$model->id);
            }
            //save country
            $locationsId[] = Yii::$app->request->post('MTopics')['locationId'];
            if ($locationsId) {
                 \app\helpers\TopicsHelper::saveOrUpdateLocationId($locationsId,$model->id);
            }
            // save dictionaries
            $sheetIds = Yii::$app->request->post('MTopics')['dictionaryId'];
            if ($sheetIds) {
                $dictionariesProperty = $drive->getDictionariesByIdsForTopic($sheetIds);
                $dictionaries = \app\helpers\TopicsHelper::saveOrUpdateDictionaries($dictionariesProperty);
                // get words and dictionaries names
                $content = $drive->getContentDictionaryByTitle($dictionaries);
                // save words and his relations with topics
                \app\helpers\TopicsHelper::saveOrUpdateDictionariesWords($content);   
                // relation topic
                \app\helpers\TopicsHelper::saveOrUpdateTopicsDictionaries($sheetIds,$model->id);   
            }
            //save urls 
            $urls = Yii::$app->request->post('MTopics')['urls'];
            if ($urls) {
                \app\helpers\TopicsHelper::saveOrUpdateUrls($urls,$model->id); 
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', [
            'model' => $model,
            'drive' => $drive,
        ]);
    }

    /**
     * Updates an existing MTopics model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $drive   = new \app\models\api\DriveApi();
        // formateer to form
        date_default_timezone_set('UTC');
        $model->end_date = date('Y-m-d',$model->end_date);
        // adding urls to form
        $model->urls = $model->urlsTopics;

        if ($model->load(Yii::$app->request->post())) {
            //save end date unix
            $model->end_date = Yii::$app->formatter->asTimestamp($model->end_date);
            $model->userId = Yii::$app->user->getId();
            // save topic
            $model->save();
            // save resourceIds
            $resourcesId = Yii::$app->request->post('MTopics')['resourceId'];
            if ($resourcesId) {
                \app\helpers\TopicsHelper::saveOrUpdateResourceId($resourcesId,$model->id);
            }else{
                \app\modules\topic\models\MTopicResources::deleteAll('topicId ='.$model->id);
            }
            //save country
            $locationsId[] = Yii::$app->request->post('MTopics')['locationId'];
            if ($locationsId) {
                 \app\helpers\TopicsHelper::saveOrUpdateLocationId($locationsId,$model->id);
            }else{
                \app\modules\topic\models\MTopicsLocation::deleteAll('topicId ='.$model->id);
            }
            // save dictionaries
            $sheetIds = Yii::$app->request->post('MTopics')['dictionaryId'];
            if ($sheetIds) {
                $dictionariesProperty = $drive->getDictionariesByIdsForTopic($sheetIds);
                $dictionaries = \app\helpers\TopicsHelper::saveOrUpdateDictionaries($dictionariesProperty);
                // get words and dictionaries names
                $content = $drive->getContentDictionaryByTitle($dictionaries);
                // save words and his relations with topics
                \app\helpers\TopicsHelper::saveOrUpdateDictionariesWords($content);   
                // relation topic
                \app\helpers\TopicsHelper::saveOrUpdateTopicsDictionaries($sheetIds,$model->id);   
            }else{
                \app\modules\topic\models\MTopicsDictionary::deleteAll('topicId ='.$model->id);
            }
            //save urls 
            $urls = Yii::$app->request->post('MTopics')['urls'];
            if ($urls) {
                \app\helpers\TopicsHelper::saveOrUpdateUrls($urls,$model->id); 
            }else{
                \app\modules\topic\models\MUrlsTopics::deleteAll('topicId ='.$model->id);
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'drive' => $drive,
        ]);
    }

    /**
     * Deletes an existing MTopics model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }


    /**
     * Finds the MTopics model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MTopics the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MTopics::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
