{*
* @module       Advanced search (AmbJoliSearch)
* @file         configure.tpl
* @subject      template pour paramétrage du module sur le 'back office'
* @copyright    Copyright (c) 2013-2019 Ambris Informatique SARL (http://www.ambris.com/)
* @author       Richard Stefan (@RicoStefan)
* @license      Commercial license
* Support by mail: support@ambris.com
*}
<div id="modulecontent" class="clearfix">

    <!-- Nav tabs -->
    <div class="col-lg-2">
        <div class="list-group">
            <a href="#dropdown_settings" class="list-group-item" data-toggle="tab">{l s='Dropdown list settings' mod='ambjolisearch'}</a>
            <a href="#results_page_settings" class="list-group-item" data-toggle="tab">{l s='Search results page settings' mod='ambjolisearch'}</a>
            <a href="#search_settings" class="list-group-item" data-toggle="tab">{l s='Search settings' mod='ambjolisearch'}</a>
        </div>
    </div>
    <!-- Tab panes -->
    <div class="tab-content col-lg-10">
        <div class="tab-pane active" id="dropdown_settings">
            {$forms.design_settings nofilter}
            {$forms.dropdown_list_settings nofilter}
            {$forms.priority_settings nofilter}
        </div>

        <div class="tab-pane" id="results_page_settings">
            {$forms.results_page_settings nofilter}
        </div>

        <div class="tab-pane" id="search_settings">
            {$forms.search_settings nofilter}
            {if isset($forms.compatibility_settings)}
                {$forms.compatibility_settings nofilter}
            {/if}
        </div>
    </div>
</div>

<script type="text/javascript">
    var toggleCategoriesOptions = function () {
        if ($('input#AJS_DISPLAY_CATEGORY_on').is(':checked')) {
            $('input[name=AJS_SHOW_PARENT_CATEGORY]').parents('.form-group').show();
            $('input[name=AJS_FILTER_ON_PARENT_CATEGORY]').parents('.form-group').show();
        } else {
            $('input[name=AJS_SHOW_PARENT_CATEGORY]').parents('.form-group').hide();
            $('input[name=AJS_FILTER_ON_PARENT_CATEGORY]').parents('.form-group').hide();
        }
    }

    var toggleApproximativeOptions = function() {
        if ($('input#AJS_APPROXIMATIVE_SEARCH_on').is(':checked')) {
            $('input[name=AJS_USE_APPROXIMATIVE_FOR_REFERENCES]').parents('.form-group').show();
        } else {
            $('input[name=AJS_USE_APPROXIMATIVE_FOR_REFERENCES]').parents('.form-group').hide();
        }

    }

    $(document).ready(function() {
        toggleCategoriesOptions();
        toggleApproximativeOptions();

        $('input[name=AJS_DISPLAY_CATEGORY]').on('change', toggleCategoriesOptions);
        $('input[name=AJS_APPROXIMATIVE_SEARCH]').on('change', toggleApproximativeOptions);

        $('#modulecontent form').on('submit', function(e) {
            var serializedData = '';
            $('#modulecontent form').each(function() {
                serializedData = serializedData + (serializedData.length > 0 ? '&' : '') + $(this).serialize();
            });

            $.ajax({
                url: $(this).attr('action'),
                data: serializedData,
                type: 'POST',
                success: function() {
                    //window.location.reload();
                    window.location.replace(window.location.href + '&successOk');
                }
            });

            return false;
        });
    });
</script>
