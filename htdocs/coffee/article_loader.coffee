
class @ArticleLoader extends CJS.Component

	constructor: (parent, id) ->
		super(parent, id)
		@offset = 8
		@blocked = no
		@showLink = no

	setShowLink: (@showLink) -> @

	setMenuId: (@menuId) -> @

	getFullUrl: (url) -> window.location.href.substr(0, window.location.href.length-5) + '/' + url + '.html'

	run: ->
		$(document).bind 'scroll', (event) =>
			if not @blocked and document.body.scrollHeight/2 < document.body.scrollTop
				@blocked = yes
				@sendRequest('loadArticle', {menuId: @menuId, offset: @offset}, @loadArticleResponse)

	loadArticleResponse: (response) ->
		if response.length
			@offset += response.length
			for article in response
				html = '<div class="box">' + article.text + '<div class="clear"></div>'
				html += '<a href="' + @getFullUrl(article.url) + '" onclick="return (new ArticleLoader()).setBaseUrl(\'' + @getBaseUrl() + '\').loadArticleById(this, ' + article.id + ');" class="doShowReply">Zobrazit celý článek</a>' if @showLink
				html += '</div>'
				$('#articles').append(html)
			@blocked = no

	loadArticleById: (@element, articleId) ->
		@element.dataset.articleId = articleId
		@sendRequest('loadArticleById', {id: articleId}, @loadArticleByIdResponse)
		no

	loadArticleByIdResponse: (response) ->
		window.oldHtml = {} if not window.oldHtml?
		window.oldHtml[@element.dataset.articleId] = $(@element).parent().html()
		newHtml = response.text + '<p><a href="#" onClick="$(this).parent().parent().html(oldHtml[' + @element.dataset.articleId + ']);return false;" class="doShowReply">Zobrazit náhled článku</a></p>'
		$(@element).parent().html(newHtml)
