<div class="modal fade" id="configuratorPreviewModalConfirmation" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
            
		<form id="form_add_configurator_to_cart_modal">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						{$productObject->name|escape:'html':'UTF-8'}
					</h5>
				</div>
				<div class="modal-body">

					{if Configuration::get('CONFIGURATOR_MODAL_CONFIRMATION_CART')}
						<table class="table table-striped">
							<tbody>
							{foreach $cartDetail as $step}
								{assign var=display value=false}
								{foreach $step.options as $option}
									{if $option.selected || !empty($option.value)}
										{assign var=display value=true}
									{/if}
								{/foreach}
								{if $step.displayed_in_preview && $display}
									<tr id="#step_{$step.id|escape:'htmlall':'UTF-8'}">
										<th>
											<strong>{$step.name|escape:'html':'UTF-8'} : </strong>
										</th>
										<td>
											{assign var=k value=0}
											{foreach $step.options as $option}
												{if !empty($option.value) || is_numeric($option.value)}
													<span class="option_value">
														{if $k > 0}, {/if}
														{$option.name|escape:'html':'UTF-8'} : {$option.value|escape:'html':'UTF-8'}{$step.input_suffix|escape:'htmlall':'UTF-8'}
													</span>
													{assign var=k value=$k+1}
												{elseif $option.selected}
													<span class="option">
														{if $k > 0}, {/if}
														{if $step.use_qty}
															{$option.qty|intval}x&nbsp;
														{/if}
														{$option.name|escape:'html':'UTF-8'}
													</span>
													{assign var=k value=$k+1}
												{/if}
											{/foreach}
										</td>
									</tr>
								{/if}
							{/foreach}
							</tbody>
							{if $priceDisplay >= 0 && $priceDisplay <= 2}
								<tfoot>
								{if $productPrice-$productPriceWithoutReduction neq 0}
									<tr>
										<th>
											{l s='Base price' mod='configurator'}
											{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
												({if $priceDisplay == 1}{l s='tax excl.' mod='configurator'}{else}{l s='tax incl.' mod='configurator'}{/if})
											{/if}
										</th>
										<td>
											<strong>{Tools::displayPrice($productPriceWithoutReduction)}</strong>
										</td>
									</tr>
									<tr>
										<th class="advantage">
											{l s='After reducing your advantage' mod='configurator'}
										</th>
										<td class="advantage">
											<strong>{Tools::displayPrice($productPrice-$productPriceWithoutReduction)}</strong>
										</td>
									</tr>
								{/if}
								<tr class="table-info">
									<th>
										{l s='Final price' mod='configurator'}
										{if $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) || !isset($display_tax_label))}
											({if $priceDisplay == 1}{l s='tax excl.' mod='configurator'}{else}{l s='tax incl.' mod='configurator'}{/if})
										{/if}
									</th>
									<td>
										<strong>{Tools::displayPrice($productPrice)}</strong>
									</td>
								</tr>
								</tfoot>
							{/if}
						</table>
					{/if}

					{if Configuration::get('CONFIGURATOR_MODAL_CONFIRMATION_CART_ACCEPTATION')}
						<div class="alert alert-info">
							<p>{l s='The right of withdrawal does not apply to the realization of tailor-made articles.' mod='configurator'}</p>
							<label for="configurator_confirmation_checkbox" style="float: none;">
								<input type="checkbox" required="true" id="configurator_confirmation_checkbox">
								{l s='I am informed that the right of withdrawal does not apply to this article' mod='configurator'}
							</label>
						</div>
					{/if}
				</div>
				<div class="modal-footer">
					<div class="row">
						<div class="col-sm-6">
							<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
								{l s='Return to product' mod='configurator'}
							</button>
						</div>
						<div class="col-sm-6">
							<button class="btn btn-sm btn-primary" type="submit" id="add_configurator_to_cart_modal">
								{l s='I confirm my configuration' mod='configurator'}
							</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>