<style type="text/css">
	#data-table-{$ID}					{ width:<% if $Width %>$Width<% else %>100%<% end_if %>; }
<% if $BorderColor %>
	#data-table-{$ID} table,
	#data-table-{$ID} th,
	#data-table-{$ID} td				{ border-color:$BorderColor; border-width:1px; border-style:solid; border-collapse:collapse; }
<% end_if %>
<% if $CellPadding %>
	#data-table-{$ID} th,
	#data-table-{$ID} td				{ padding:$CssPadding; }
<% end_if %>
	#data-table-{$ID} td[align="center"] { text-align:center; }
	#data-table-{$ID} td[align="right"] { text-align:right; }
</style>

<div class="data-table-wrap" id="data-table-{$ID}">
	<table class="data-table<% if $BorderColor %> border<% end_if %><% if $Striped %> striped<% end_if %>" cellpadding="$CellPadding" cellspacing="0" border="<% if $BorderColor %>1<% else %>0<% end_if %>">
		<% if $FirstRowHeader %>
			<thead>
				<tr>
					<% with $HeaderRow %>
						<% loop $DataTableColumns %>
							<th align="$Align.LowerCase"><% if $Content %>$Content.RAW<% else %>&nbsp;<% end_if %></th>
						<% end_loop %>
					<% end_with %>
				</tr>
			</thead>
		<% end_if %>
		<tbody>
			<% loop $BodyRows %>
				<tr class="$EvenOdd">
					<% loop $BodyColumns %>
						<td<% if $DataTableRow.DataTable.FirstRowHeader %> data-title="$ParentColumn.Content"<% end_if %> align="$Align.LowerCase"<% if not $Content %> data-empty="1"<% end_if %>>
							<% if $Content %>$Content.RAW<% else %>&nbsp;<% end_if %>
						</td>
					<% end_loop %>
				</tr>
			<% end_loop %>
		</tbody>
	</table>
</div>
