{*
* @module       Advanced search (AmbJoliSearch)
* @file         synonyms.tpl
* @subject      template pour paramétrage du module sur le 'back office'
* @copyright    Copyright (c) 2013-2022 Ambris Informatique SARL (http://www.ambris.com/)
* @author       Richard Stefan (@RicoStefan)
* @license      Commercial license
* Support by mail: support@ambris.com
*}

<div class="col-xs-12">
    <div style="float:left;width:25%">No approximative search<br />Best performance</div>
    <div style="float:left;width:10%">&nbsp;</div>
    <div style="float:left;width:30%;text-align:center">Medium approximative search<br />Medium performance</div>
    <div style="float:left;width:10%;text-align:right;">&nbsp;</div>
    <div style="float:left;width:25%; text-align:right;">Strong approximative search<br />Worst performance</div>
</div>
<div class="slidecontainer">
  <input type="range" name="AJS_APPROXIMATION_LEVEL" min="0" max="4" value="{$approximation_level}" class="slider" id="myRange">
</div>
<div class="col-xs-12">
    <div style="float:left;width:25%">0</div>
    <div style="float:left;width:10%">1</div>
    <div style="float:left;width:30%;text-align:center">2</div>
    <div style="float:left;width:10%;text-align:right;">3</div>
    <div style="float:left;width:25%; text-align:right;">4</div>
</div>