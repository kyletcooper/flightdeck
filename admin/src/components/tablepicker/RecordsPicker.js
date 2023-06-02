import React, { useEffect, useState } from "react";
import TablePicker from "./TablePicker";
import RowsPicker from "./RowsPicker";
import Icon from "../Icon";

export default function RecordsPicker({
	onChange = () => { },
}) {
	const [expandedTable, setExpandedTable] = useState(false);
	const [selectedTables, setSelectedTables] = useState([]);
	const [selectedRows, setSelectedRows] = useState([]);

	const expandTable = (table) => {
		setExpandedTable(table);
	}

	const collapseTable = () => {
		setExpandedTable(false);
	}

	useEffect(() => {
		let selection = [];

		if (expandedTable) {
			let selectedRowsTable = {
				table: expandedTable.name,
				rows: []
			}

			selectedRows.forEach(row => {
				selectedRowsTable.rows.push(row);
			})

			selection.push(selectedRowsTable);
		}
		else {
			selectedTables.forEach(table => {
				selection.push({
					table: table,
					rows: -1
				});
			})
		}

		onChange(selection);
	}, [expandedTable, selectedTables, selectedRows])

	if (expandedTable) {
		return (
			<div className="flex flex-col h-full">
				<div className="p-5 border-b border-gray-300">
					<button className="flex items-center gap-3 text-blue-500" onClick={collapseTable}>
						<Icon icon="chevron_left" />
						All tables
					</button>

					<h2 className="text-3xl font-semibold">
						{expandedTable.name}
					</h2>

				</div>

				<RowsPicker onChange={setSelectedRows} table={expandedTable.name} primaryKey={expandedTable.primary_key} className="grow" />
			</div>
		)
	}
	else {
		return <TablePicker onChange={setSelectedTables} onExpand={expandTable} />
	}
}