<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command save on mentions table Faker Data
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {   
        // use the factory to create a Faker\Generator instance
        $faker = \Faker\Factory::create();
        
        for ($i=0; $i < 1000 ; $i++) { 
            $model = new \app\models\Mentions();
            $model->alert_mentionId = 733;
            $model->origin_id = 5007;
            $model->created_time = $faker->unixTime($max = 'now');
            $model->mention_data = ['retweet_count' => 0,'favorite_count' => 0];
            $model->subject = $faker->sentence($nbWords = 6, $variableNbWords = true);
            $model->message = $faker->sentence($nbWords = 6, $variableNbWords = true);
            $model->message_markup = $faker->sentence($nbWords = 6, $variableNbWords = true);
            $model->url = $faker->url;

            $model->save();
        }
       

        return ExitCode::OK;
    }
}
