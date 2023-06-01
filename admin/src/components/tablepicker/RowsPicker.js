import React, { useEffect, useState } from "react";
import { wp_rest_api } from "../../helpers/wpajax";
import Icon from "../Icon";

export default function RowsPicker({
	table,
	primaryKey,
	onChange,
	perPage = 15,
	className = '',
}) {
	if (!table) return;

	const [selection, setSelection] = useState([]);
	const [maxPage, setMaxPage] = useState(1);
	const [page, setPage] = useState(1);
	const [rows, setRows] = useState([]);

	useEffect(() => {
		wp_rest_api(`flightdeck/v1/tables/${table}`, 'GET', {
			'per_page': perPage,
			'page': page,
		}).then(resp => {
			setMaxPage(resp.headers.get('X-WP-TotalPages'));
			return resp.json();
		}).then(setRows);
	}, [table, page]);

	const hasPrevPage = page >= 2;
	const hasNextPage = page < maxPage;

	const nextPage = () => {
		const newPage = Math.min(page + 1, maxPage);

		if (newPage !== page) {
			setPage(newPage);
		}
	}

	const prevPage = () => {
		const newPage = Math.max(page - 1, 1);

		if (newPage !== page) {
			setPage(newPage);
		}
	}

	const spacingClasses = (i, keys) => {
		return 'p-2 ' + (i === keys.length ? 'pr-5 ' : null);
	}

	const getRowID = (row) => {
		return row[primaryKey];
	}

	const isRowSelected = (row) => {
		const rowID = getRowID(row);
		return selection.includes(rowID);
	}

	const toggleRowSelected = (row) => {
		const rowID = getRowID(row);
		const newSelection = selection.includes(rowID) ? selection.filter(el => el !== rowID) : [...selection, rowID];
		setSelection(newSelection);
		onChange(newSelection);
	}

	return (
		<div className={className + ' flex flex-col max-w-full overflow-x-auto'}>
			<table className="w-full text-sm">
				<thead className="border-b border-gray-300">
					<tr>
						<th></th>

						{
							rows[0] && Object.keys(rows[0]).map((key, i, keys) => (
								<th key={key} className={spacingClasses(i, keys) + ' font-medium text-black'}>
									{key}
								</th>
							))
						}
					</tr>
				</thead>

				<tbody>
					{rows.map((row, i) => (
						<tr key={i} className={isRowSelected(row) ? 'text-black bg-blue-100' : 'text-gray-700'}>
							<td>
								<label className="p-2 pl-5">
									<input type="checkbox" checked={isRowSelected(row)} onChange={() => toggleRowSelected(row)} />
								</label>
							</td>

							{
								Object.keys(row).map((key, i, keys) => (
									<td key={key} className={spacingClasses(i, keys)}>
										{row[key]}
									</td>
								))
							}
						</tr>
					))}
				</tbody>
			</table>

			<footer className="flex items-center px-5 py-2 mt-auto sticky left-0 bottom-0 w-full border-t border-gray-300">
				<span className="mr-auto">
					Page {page} of {maxPage}
				</span>

				<button aria-label="Previous page" onClick={prevPage} disabled={!hasPrevPage} >
					<Icon icon="chevron_left" />
				</button>

				<button aria-label="Next page" onClick={nextPage} disabled={!hasNextPage}>
					<Icon icon="chevron_right" />
				</button>

			</footer>
		</div>
	)
}