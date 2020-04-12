<?php

  function getData($url2){

    $client2 = curl_init($url2);
    curl_setopt($client2,CURLOPT_RETURNTRANSFER,true);
    $response2 = curl_exec($client2);

    return json_decode($response2);
  };

  $url = "https://api.kawalcorona.com/indonesia/";

  $client = curl_init($url);
  curl_setopt($client,CURLOPT_RETURNTRANSFER,true);
  $response = curl_exec($client);

  $result = json_decode($response);

  $natConfirmed = $result[0]->positif;

  $natDeaths = $result[0]->meninggal;

  $natRecovered = $result[0]->sembuh;

  $intConfirmed = getData("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/Coronavirus_2019_nCoV_Cases/FeatureServer/1/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22Confirmed%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true");

  $intDeaths = getData("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/Coronavirus_2019_nCoV_Cases/FeatureServer/1/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22Deaths%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true");

  $intRecovered = getData("https://services1.arcgis.com/0MSEUqKaxRlEPj5g/arcgis/rest/services/Coronavirus_2019_nCoV_Cases/FeatureServer/1/query?f=json&where=1%3D1&returnGeometry=false&spatialRel=esriSpatialRelIntersects&outFields=*&outStatistics=%5B%7B%22statisticType%22%3A%22sum%22%2C%22onStatisticField%22%3A%22Recovered%22%2C%22outStatisticFieldName%22%3A%22value%22%7D%5D&cacheHint=true");

  $interConfirmed = $intConfirmed->features[0]->attributes->value;
  $interDeaths = $intDeaths->features[0]->attributes->value;
  $interRecovered = $intRecovered->features[0]->attributes->value;
?>