<table id="table3">
	<thead>
		<tr>
			<th>
				{@labels.default}
			</th>
			<th class="medium-column">
				{@labels.color}
			</th>
			<th>
				${LangLoader::get_message('form.name', 'common')}
			</th>
		</tr>
	</thead>
	<tbody>
		# START severities #
		<tr>
			<td>
				<div class="form-field-radio">
					<input aria-label="{severities.NAME}" id="default_severity{severities.ID}" type="radio" name="default_severity" value="{severities.ID}"# IF severities.C_IS_DEFAULT # checked="checked"# ENDIF # />
					<label for="default_severity{severities.ID}"></label>
				</div>
			</td>
			<td>
				<input type="color" name="color{severities.ID}" id="color{severities.ID}" value="{severities.COLOR}" />
			</td>
			<td>
				<input type="text" name="severity{severities.ID}" value="{severities.NAME}" />
			</td>
		</tr>
		# END severities #
	</tbody>
		# IF C_DISPLAY_DEFAULT_DELETE_BUTTON #
	<tfoot>
	<tr>
		<td colspan="3">
			<a href="{LINK_DELETE_DEFAULT}" aria-label="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="fa fa-delete"></i> {@labels.del_default_value}</a>
		</td>
	</tr>
	</tfoot>
		# ENDIF #
</table>
