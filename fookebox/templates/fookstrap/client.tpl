<!DOCTYPE html>

<html lang="en">
	<head>
		<title>fookebox</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<link rel="stylesheet" href="/css/bootstrap/bootstrap.min.css" />
		<link rel="stylesheet" href="/css/fookstrap/fookebox.css" />

		<script src="/js/jquery/jquery-1.11.1.min.js"></script>
		<script src="/js/bootstrap/bootstrap.min.js"></script>
		<script src="/js/fookebox/fookstrap/fookebox.js"></script>
		<script src="/js/fookebox/fookstrap/effects.js"></script>
	</head>
	<body>
		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<p class="navbar-text now-playing visible-xs"><span class="currentArtist"></span> &ndash; <span class="currentTitle"></span></p>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a data-toggle="tab" href="#artists">Artists</a></li>
						<li><a href="#genres" data-toggle="tab">Genres</a></li>
						<li class="dropdown">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">Queue <span class="label label-info" id="queueStatus"></span></a>
							<ul class="dropdown-menu" role="menu" id="queue">
% for i in range(0, int(config.get('max_queue_length'))):
								<li class="disabled"><a role="menuitem" tabindex="-1"><span class="artist"></span> &ndash; <span class="title"></span></a></li>
% endfor
							</ul>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right hidden-xs">
						<p class="navbar-text now-playing">Now playing: <span class="currentArtist"></span> &ndash; <span class="currentTitle"></span></p>
					</ul>
				</div>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-3 col-md-2 sidebar">
					<div class="tab-content">
						<div class="tab-pane active" id="artists">
							<form class="form" id="artistSearchForm">
								<input type="text" class="form-control" placeholder="Search artists" id="artistSearch">
							</form>
							<ul class="nav nav-pills nav-stacked nav-sidebar">
% for artist in artists:
								<li class="artist"><a href="#" data-base64="${artist.base64}">${artist.name}</a></li>
% endfor
							</ul>
						</div>
						<div class="tab-pane" id="genres">
							<form class="form" id="genreSearchForm">
								<input type="text" class="form-control" placeholder="Search genres">
							</form>
							<ul class="nav nav-pills nav-stacked nav-sidebar">
% for genre in genres:
								<li class="genre"><a href="#" data-base64="${genre.base64}">${genre.name}</a></li>
% endfor
							</ul>
						</div>
					</div>
				</div>
				<div class="col-sm-9 col-md-10 col-xs-offset-0 col-sm-offset-3 col-md-offset-2 main">
					<div id="result" class="panel panel-default"></div>
				</div>
			</div>
		</div>
	</body>
</html>