import React, { useState } from "react"
import Toast from "./Toast";

export default function ToastContainer() {
	const [list, setList] = useState([]);

	window.toast = {
		create: (content, icon = "info", lifespan = 3000) => {
			const id = Date.now();

			setList([
				...list,
				{
					id: id,
					icon: icon,
					content: content
				}
			]);

			setTimeout(() => {
				window.toast.delete(id);
			}, lifespan)
		},

		delete: (id) => {
			setList(list.filter(toast => toast.id !== id));
		}
	};

	return (
		<div className="fixed w-full z-[999999] bottom-6 left-6 max-w-sm flex flex-col gap-3">
			{
				list.map((toast) => (
					<Toast key={toast.id} toast={toast} />
				))
			}
		</div>
	)
}