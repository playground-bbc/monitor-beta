<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property int $categoryId
 * @property string $name
 * @property int $status
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property ProductCategories $category
 * @property ProductsModels[] $productsModels
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categoryId','name'], 'required'],
            [['name'],\app\components\ProductsValidator::className()],
            [['categoryId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['categoryId'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategories::className(), 'targetAttribute' => ['categoryId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'categoryId' => Yii::t('app', 'Category ID'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * [getProducts get all products by categories]
     * @return [array] []
     */
    public static function getProducts()
    {
        $seriesHeader = Yii::t('app', 'Series de productos');
        $familyHeader = Yii::t('app', 'Familia de productos');
        $categoriesHeader = Yii::t('app', 'Categoria de producto');
        $productsHeader = Yii::t('app', 'Productos');
        $modelsHeader = Yii::t('app', 'Modelos de producto');
        

        $series[$seriesHeader] = ArrayHelper::map(ProductsSeries::find()
                                                ->where(['status' => 1])
                                                ->all(),
                                            'name','name');
        
        $family[$familyHeader] = ArrayHelper::map(ProductsFamily::find()->andFilterCompare('seriesId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'name','name');
        
        $categories[$categoriesHeader] = ArrayHelper::map(ProductCategories::find()
                                                ->andFilterCompare('familyId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'name','name');
        
        $products[$productsHeader] = ArrayHelper::map(Products::find()
                                                ->andFilterCompare('categoryId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'name','name');
       

        $products_models[$modelsHeader] = ArrayHelper::map(ProductsModels::find()
                                                ->andFilterCompare('productId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'name','name');

        $data = ArrayHelper::merge($series,$family);
        $data = ArrayHelper::merge($categories,$data);
        $data = ArrayHelper::merge($products,$data);
        $data = ArrayHelper::merge($products_models,$data);
        return $data;

    }

    /**
     * @param  array
     * @return [array]
     */
    public static function getModelsIdByName($products=[])
    {   
        $models_products = [];
        for ($i=0; $i <sizeof($products) ; $i++) { 
            
            $products_series = ProductsSeries::findOne(['name' => $products[$i]]);
            
            if ($products_series) {

                $products_family = ArrayHelper::map(
                                                ProductsFamily::find()->where(['seriesId' => $products_series->id ])->all(),
                                                'id','seriesId');
               
                $products_categories  =  ArrayHelper::map(
                                                ProductCategories::find()->where(['products_familyId' => array_keys($products_family)])->all(),
                                                'id','products_familyId');  
                
                $productsId  =  ArrayHelper::map(
                                                Products::find()->where(['categoryId' => array_keys($products_categories)])->all(),
                                                'id','name');
                $modelsId   = ArrayHelper::map(
                                                ProductsModels::find()->where(['productId' => array_keys($productsId)])->all(),
                                                'id','name');                                  
            }

            $products_family = ArrayHelper::map(
                                                ProductsFamily::find()->where(['name' => $products[$i]])->all(),
                                                'id','seriesId');

            if ($products_family) {
                $products_categories  =  ArrayHelper::map(
                                                ProductCategories::find()->where(['products_familyId' => array_keys($products_family)])->all(),
                                                'id','products_familyId');  
                
                $productsId  =  ArrayHelper::map(
                                                Products::find()->where(['categoryId' => array_keys($products_categories)])->all(),
                                                'id','name');
                $modelsId   = ArrayHelper::map(
                                                ProductsModels::find()->where(['productId' => array_keys($productsId)])->all(),
                                                'id','name');  
            }
            
            $categoryId = ArrayHelper::map(
                                            ProductCategories::find()->where(['name' => $products[$i]])->all(),
                                            'id','products_familyId');

            if ($categoryId) {
                $productsId = ArrayHelper::map(
                                            Products::find()->where(['categoryId' => array_keys($categoryId)])->all(),
                                            'id','categoryId');
                $modelsId  =  ArrayHelper::map(
                                            ProductsModels::find()->where(['productId' => array_keys($productsId)])->all(),
                                            'id','name');
            }

            $productsId = ArrayHelper::map(
                                            Products::find()->where(['name' => $products[$i]])->all(),
                                            'id','categoryId');
           
            if ($productsId) {
                $modelsId  =  ArrayHelper::map(ProductsModels::find()->where(['productId' => array_keys($productsId)])->all(),'id','name');
            }

            $models  =  ArrayHelper::map(ProductsModels::find()->where(['name' => $products[$i]])->all(),'id','name');
            if ($models) {
                $modelsId = $models;
            }

            array_push($models_products, $modelsId);
        }

        $temp = [];
        for ($i=0; $i <sizeof($models_products) ; $i++) { 
            foreach ($models_products[$i] as $key => $value) {
                if (!in_array($key, $temp)) {
                    $temp[$key] = $value;
                }
            }
        }
        $models_products = $temp;
        return $models_products;
    }

    public static function saveProductsModelAlerts($products_models = [],$alertId){
        $productsIds = self::getModelsIdByName($products_models);
        
        foreach ($productsIds as $productId => $name) {
            $model = new \app\models\ProductsModelsAlerts();
            $model->alertId = $alertId;
            $model->product_modelId = $productId;
            $model->save();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProductCategories::className(), ['id' => 'categoryId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsModels()
    {
        return $this->hasMany(ProductsModels::className(), ['productId' => 'id']);
    }
}
