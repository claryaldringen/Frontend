
import Banner from './banner'
import Concert from './concert'
import ArticleLoader from './article_loader'
import Document from './ComponentJS/document'
import Discussion from './discussion'

$(document).ready () ->
	banner = new Banner()
	banner.render()
	if component?
		if component is 'concert'
			Concert.getInstance().setBaseUrl(baseUrl).setMenuId(menuId).load()
		if component is 'article'
			articleLoader = new ArticleLoader();
			articleLoader.setBaseUrl(baseUrl).setMenuId(menuId).run()
		if component is 'discussion'
			doc = new Document()
			discussion = new Discussion('discussion', doc.render())
			discussion.setBaseUrl(baseUrl).setMenuId(menuId).load();

	$('.doOpenArticle').click (event) ->
		articleLoader.loadArticleById this, this.dataset.id
		return false


$(document).bind 'scroll', () ->
	percent = document.body.scrollTop / document.body.clientHeight;
	fixed = false;
	if window.devicePixelRatio < 1.6
		if document.body.clientWidth > 640 && percent > 0.19
			fixed = true
		else if document.body.clientWidth < 640 && percent > 0.14
			fixed = true
	else
		fixed = percent > 0.09

	if fixed && !$('#menu').hasClass('fixed')
		$('#menu').addClass('fixed')
	else if(!fixed && $('#menu').hasClass('fixed'))
		$('#menu').removeClass('fixed')
