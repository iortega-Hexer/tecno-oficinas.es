{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author DMConcept <support@dmconcept.fr>
*  @copyright 2015 DMConcept
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<html>
    <head>
        <link rel="stylesheet" href="../modules/configurator/views/css/bootstrap.min.css">
    </head>
    <body>
        
        {if $end}
            <h3 class="text-center">{l s='Deleting ...' mod='configurator'} {{$progress}}% <small>{{$counters.current}} / {{$counters.total}} {l s='attribute(s)' mod='configurator'}</small></h3>
            <div class="progress">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="{{$progress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$progress}}%">
                  <span class="sr-only">{{$progress}}% Complete (success)</span>
                </div>
            </div>
        {else if $run}
            <h3 class="text-center">{l s='Deleting ...' mod='configurator'} {{$progress}}% <small>{{$counters.current}} / {{$counters.total}} {l s='attribute(s)' mod='configurator'}</small></h3>
            <div class="progress">
                <div class="progress-bar progress-bar-warning progress-bar-striped" role="progressbar" aria-valuenow="{{$progress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$progress}}%">
                  <span class="sr-only">{{$progress}}% Complete (success)</span>
                </div>
            </div>
        {else}
            <div class="alert alert-warning">
                {l s='You have %s attribute(s) to delete.' mod='configurator' sprintf=$counters.total}<br>
                <a href="{{$run_remove_attributes_link}}" class="btn btn-warning">
                    <i class="icon-trash"></i>
                    {l s='Delete' mod='configurator'}
                </a>
            </div>
        {/if}

        {if $run && !$end}
            <script>
                location.href = "{{$run_remove_attributes_link}}";
            </script>
        {/if}

    </body>
</html>