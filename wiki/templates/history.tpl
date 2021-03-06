	<section id="module-wiki-history" class="wiki-history">
		# IF C_ARTICLE #
		<h1>{L_HISTORY} : <a href="{U_ARTICLE}">{TITLE}</a></h1>
		<table id="table">
			<thead>
				<tr>
					<th>
						{L_VERSIONS}
					</th>
					<th>
						{L_DATE}
					</th>
					<th>
						{L_CHANGE_REASON}
					</th>
					<th>
						{L_AUTHOR}
					</th>
					<th>
						{L_ACTIONS}
					</th>
				</tr>
			</thead>
			<tbody>
				# START list #
				<tr>
					<td>
						<a href="{list.U_ARTICLE}">{list.TITLE}</a>
						<span class="infos-options">{list.CURRENT_RELEASE}</span>
					</td>
					<td>
						{list.DATE}
					</td>
					<td class="col-max">
						{list.CHANGE_REASON}
					</td>
					<td>
						{list.AUTHOR}
					</td>
					<td>
						{list.ACTIONS}
					</td>
				</tr>
				# END list #
			</tbody>
		</table>
		# ELSE #
		<h1>{L_HISTORY}</h1>
		<table id="table2">
			<caption>{L_HISTORY}</caption>
			<thead>
				<tr>
					<th>
						<a href="{TOP_TITLE}" class="fa fa-table-sort-up"></a>
						{L_TITLE}
						<a href="{BOTTOM_TITLE}" class="fa fa-table-sort-down"></a>
					</th>
					<th>
						<a href="{TOP_DATE}" class="fa fa-table-sort-up"></a>
						{L_DATE}
						<a href="{BOTTOM_DATE}" class="fa fa-table-sort-down"></a>
					</th>
					<th>
						{L_AUTHOR}
					</th>
				</tr>
			</thead>
			<tbody>
				# START list #
				<tr>
					<td>
						<a href="{list.U_ARTICLE}">{list.TITLE}</a>
					</td>
					<td>
						{list.DATE}
					</td>
					<td>
						{list.AUTHOR}
					</td>
				</tr>
				# END list #
			</tbody>
			# IF C_PAGINATION #
			<tfoot>
				<tr>
					<td colspan="3">
						# INCLUDE PAGINATION #
					</td>
				</tr>
			</tfoot>
			# ENDIF #
		</table>
		# END IF #
	</section>
