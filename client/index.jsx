
import React from 'react'
import ReactDOM from 'react-dom'
import 'react-photoswipe/lib/photoswipe.css';

import { PhotoSwipe, PhotoSwipeGallery } from 'react-photoswipe';

class App extends React.Component {

	getThumbnailContent = (item) => {
		return (
			<img src={item.thumbnail} />
		);
	};
	render() {
		return (
			<div>
				<PhotoSwipeGallery items={galleryItems} thumbnailContent={this.getThumbnailContent} />
			</div>
		);
	}
}

function run() {
	var appEl = document.getElementById('app');
	if(appEl) {
		ReactDOM.render(<App />, appEl);
	}
	var showMenu = false;
	var el = document.getElementById('menuico');
	var menuEl = document.getElementById('menu');
	el.addEventListener('click', () => {
		showMenu = !showMenu;
		menuEl.style.display = showMenu ? 'block' : 'none';
	});
}

if (window.addEventListener) {
  window.addEventListener('DOMContentLoaded', run);
} else {
  window.attachEvent('onload', run);
}


