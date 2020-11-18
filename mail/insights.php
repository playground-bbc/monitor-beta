<?php 
$titleInsights = [
    //facebook
    "page_impressions"=> "Impresiones",
    "page_post_engagements"=> "Interaciones",
    "page_impressions_unique"=> "Alcance total diario",
    "fan_count"=> "Me gusta la Pagina ",
    // instagram
    "reach"=> "Alcance",
    "impressions"=> "Impresiones",
    "profile_views"=> "Visitas al perfil",
    "follower_count"=> "Nuevos seguidores",
    "followers_count"=> "Total de Seguidores",
];

$logoUrl = [
    'Instagram' => 'https://www.dropbox.com/s/z0143n809glxsx4/Instagram.png?raw=1',
    'Facebook' => 'https://www.dropbox.com/s/8k0mz9t5t0u9e3k/Facebook.png?raw=1',
    'logo' => 'https://www.dropbox.com/s/ycqnfcyu931x2ov/logo.png?raw=1',
    'logoEmpresa' => 'https://www.dropbox.com/s/5kpxs768su8i6im/logoEmpresa.png?raw=1'
];
$headersPost = [
    //facebook
    "post_impressions"=> "Impresiones",
    "post_engaged_users"=> "Interaciones",
    "post_reactions_by_type_total"=> "likes/reacciones",
    // instagram
    "impressions"=> "Impresiones",
    "reach"=> "Alcance",
    "engagement"=> "Interacción",
    "likes"=> "Me Gusta",
   // "coments"=> "Comentarios y respuestas",
];


?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
   <head>
      <title></title>
      <!--[if !mso]><!-- -->
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!--<![endif]-->
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <style type="text/css">#outlook a { padding:0; }
         body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
         table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
         img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
         p { display:block;margin:13px 0; }
      </style>
      <!--[if mso]>
      <xml>
         <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
         </o:OfficeDocumentSettings>
      </xml>
      <![endif]--><!--[if lte mso 11]>
      <style type="text/css">
         .mj-outlook-group-fix { width:100% !important; }
      </style>
      <![endif]--><!--[if !mso]><!-->
      <link href="https://fonts.googleapis.com/css2?family=Roboto" rel="stylesheet" type="text/css">
      <style type="text/css">@import url(https://fonts.googleapis.com/css2?family=Roboto);</style>
      <!--<![endif]-->
      <style type="text/css">@media only screen and (min-width:480px) {
         .mj-column-per-100 { width:100% !important; max-width: 100%; }
         }
      </style>
      <style type="text/css">@media only screen and (max-width:480px) {
         table.mj-full-width-mobile { width: 100% !important; }
         td.mj-full-width-mobile { width: auto !important; }
         }
      </style>
      <style type="text/css"></style>
   </head>
   <body style="background-color:white;">
      <div style="background-color:white;">
         <!--[if mso | IE]>
         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                         <tbody>
                                                            <tr>
                                                               <td style="width:100px;"><img alt="LG Electronics" height="auto" src="<?= $logoUrl['logoEmpresa'] ?>" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:12px;" width="100"></td>
                                                            </tr>
                                                         </tbody>
                                                      </table>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <div style="font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:34px;line-height:1;text-align:center;color:black;">Alertas en tu correo</div>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>
         <?php foreach($model as $index => $page): ?>
         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                         <tbody>
                                                            <tr>
                                                               <td style="width:550px;"><img alt="" height="auto" src="<?= $page['image_url']?>" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:12px;" width="550"></td>
                                                            </tr>
                                                         </tbody>
                                                      </table>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                         <tbody>
                                                            <tr>
                                                                <?php 
                                                                    $resourceName_explode = explode(" ",$page['resource']['name']);
                                                                    $name = (isset($resourceName_explode[0])) ? $resourceName_explode[0] : 'logo';
                                                                    $pathLogo = $logoUrl[$name];
                                                                    $hiperLink = $page['permalink'];
                                                                ?>
                                                               <td style="width:50px;"> <a href="<?= $hiperLink ?>" target="_blank"><img alt="Facebook" height="auto" src="<?= $pathLogo?>" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:12px;" width="50"></a></td>
                                                            </tr>
                                                         </tbody>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>

        
         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;border-bottom:1px solid #ccc;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="left" style="font-size:0px;padding:0;word-break:break-word;">
                                                      <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:black;font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:12px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                                         <tr>
                                                            <?php foreach($page['wInsights'] as $wInsightsIndex => $wInsight): ?>
                                                                <td style="width:20%;text-align:center;border-right:1px solid #ccc;"><?= number_format($wInsight['value'], 0, '', '.') ?></td>
                                                            <?php endforeach; ?>
                                                         </tr>
                                                         <tr>
                                                            <?php foreach($page['wInsights'] as $wInsightsIndex => $wInsight): ?>
                                                                <td style="width:20%;text-align:center;border-right:1px solid #ccc;"><?= $titleInsights[$wInsight['name']] ?></td>
                                                            <?php endforeach; ?>
                                                         </tr>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>


         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <div style="font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:18px;line-height:1;text-align:left;color:black;">Post Insights</div>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>
         
         
         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;border-top:1px solid #ccc;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:black;font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:12px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                                         <tr style="border-bottom:1px solid #ccc;padding:15px 0;">
                                                            <th style="width:20%;">Título post</th>
                                                            <th style="width:20%;">Familia Producto</th>
                                                            <?php foreach($page['posts'][0]['wInsights'] as $wInsightIndex => $wInsight): ?>
                                                                <?php if(isset($headersPost[$wInsight['name']])): ?>
                                                                    <th style="width:16.8%;"><?= $headersPost[$wInsight['name']] ?></th>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?> 
                                                         </tr>
                                                         <?php foreach($page['posts'] as $postIndex => $post): ?>
                                                            <tr style="border-bottom:1px solid #ccc;padding:15px 0;">
                                                                <td style="width:16.8%;text-align:center;"><a href="<?= $post['permalink'] ?>" target="_blank"><?= substr($post['message'],0,10) ?></a></td>
                                                                <td style="width:16.8%;text-align:center;"><mark style="background-color:#5bc0de;padding:3px;border-radius:3px;color:#fff;font-weight:bold;"><?= (isset($post['wProductsFamilyContent'][0])) ? $post['wProductsFamilyContent'][0]['serie']['abbreviation_name'] : '-' ?></mark></td>
                                                                <?php foreach($post['wInsights'] as $wInsightsIndex => $wInsight): ?>
                                                                    <?php if(isset($headersPost[$wInsight['name']])): ?>
                                                                        <?php if(!is_null($wInsight['value'])): ?> 
                                                                            <td style="width:16.8%;text-align:center;"><?= number_format($wInsight['value'], 0, '', '.')  ?></td>
                                                                        <?php else: ?>
                                                                            <td style="width:16.8%;text-align:center;"><?= $wInsight['_like'] . "/" . ($wInsight['_wow'] + $wInsight['_haha'] + $wInsight['_sorry'] + $wInsight['_anger'])  ?></td> 
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                         <?php endforeach; ?> 
                                                        
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>
         <?php if(count($page['stories'])): ?>
         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;border-top:1px solid #ccc;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <div style="font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:18px;line-height:1;text-align:left;color:black;">Stories Insights</div>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>

         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;border-top:1px solid #ccc;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <table cellpadding="0" cellspacing="0" width="100%" border="0" style="color:black;font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:12px;line-height:22px;table-layout:auto;width:100%;border:none;">
                                                         <tr style="border-bottom:1px solid #ccc;padding:15px 0;">
                                                            <th style="width:25%;">Link</th>
                                                            <th style="width:25%;">Impresiones</th>
                                                            <th style="width:25%;">Alcance</th>
                                                            <th style="width:25%;">Respuestas</th>
                                                         </tr>
                                                         <?php foreach($page['stories'] as $storiesIndex => $story): ?>
                                                            <tr style="border-bottom:1px solid #ccc;padding:15px 0;">
                                                                <td style="width:25%;text-align:center;"><a href="<?= $story['permalink'] ?>" target="_blank"><?=  \Yii::$app->formatter->asDatetime($story['timespan'])  ?></a></td>
                                                                <?php foreach($story['wInsights'] as $wInsightsIndex => $wInsight): ?>
                                                                    <td style="width:25%;text-align:center;"><?= number_format($wInsight['value'], 0, '', '.')  ?></td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                         <?php endforeach; ?>
                                                        
                                                      </table>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>
         <?php endif; ?> 
         <br><br><br><br>






        <?php endforeach; ?> 
         
         <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" >
            <tr>
               <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                  <![endif]-->
                  <div style="background:white;background-color:white;margin:0px auto;max-width:600px;">
                     <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:white;background-color:white;width:100%;">
                        <tbody>
                           <tr>
                              <td style="direction:ltr;font-size:0px;padding:50px 0 0;text-align:center;">
                                 <!--[if mso | IE]>
                                 <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                       <td class="" style="vertical-align:top;width:600px;" >
                                          <![endif]-->
                                          <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                             <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f5f5;vertical-align:top;" width="100%">
                                                <tr>
                                                   <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                         <tbody>
                                                            <tr>
                                                               <td style="width:50px;"><img alt="LG Electronics" height="auto" src="<?= $logoUrl['logoEmpresa'] ?>"  style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:12px;" width="50"></td>
                                                            </tr>
                                                         </tbody>
                                                      </table>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                                                      <div style="font-family:Roboto, Helvetica, Arial, Sans-Serif;font-size:34px;line-height:1;text-align:center;color:black;">Gracias</div>
                                                   </td>
                                                </tr>
                                             </table>
                                          </div>
                                          <!--[if mso | IE]>
                                       </td>
                                    </tr>
                                 </table>
                                 <![endif]-->
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
                  <!--[if mso | IE]>
               </td>
            </tr>
         </table>
         <![endif]-->
      </div>
   </body>
</html>