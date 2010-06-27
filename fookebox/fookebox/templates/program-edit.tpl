<%inherit file="base.tpl"/>
<div id="message" style="display: none">
	<div class="corner tl"></div>
	<div class="corner tr"></div>
	<div class="corner bl"></div>
	<div class="corner br"></div>
	<div id="messageText"></div>
</div>
<h1 id="h1"><a href="/">${config.get('site_name')}</a></h1>
<div id="meta">
	<a href="http://fookebox.googlecode.com/">fookebox</a> ${config.get('version')}<br />
	&copy; 2007-2010 <a href="http://www.ott.net/">Stefan Ott</a>
</div>
<style>
<!--
table.schedule {
	margin: 0 auto;
	border-collapse: collapse;
}

table.schedule td {
	padding: 5px 10px;
}

table.schedule tr.drop {
	background-color: blue;
}
-->
</style>
<script>
<!--
function moveEvent(data)
{
	ajax_post('schedule/move', data, function(transport)
	{
		window.location.reload();
	});
}

function moveDown(event)
{
	var data = $H({
		'id': event,
		'direction': 'down'
	});
	moveEvent(data);
}

function moveUp(event)
{
	var data = $H({
		'id': event,
		'direction': 'up'
	});
	moveEvent(data);
}

function setCurrentEvent(event)
{
	var data = $H({
		'id': event
	});

	ajax_post('schedule/current', data, function(transport)
	{
		window.location.reload();
	});
}

function deleteEvent(event)
{
	if (!confirm('Delete this event?')) {
		return;
	}

	var data = $H({
		'id': event
	});

	ajax_post('schedule/delete', data, function(transport)
	{
		window.location.reload();
	});
}

function editEvent(event)
{
	var tr = $('event-' + event);
	alert(tr);
}
-->
</script>
<table class="schedule">
	<thead>
		<tr>
			<th>time</th>
			<th>event name</th>
			<th>event type</th>
		</tr>
	</thead>
	<tbody>
<%include file="program-edit-events.tpl"/>
		<tr id="addEventRow" style="display: none">
			<form method="post" action="schedule">
				<td>
					<select name="hour">
% for hour in range(0, 24):
						<option>${"%02d" % hour}</option>
% endfor
					</select>:<select name="minute">
% for minute in range(0, 60):
						<option>${"%02d" % minute}</option>
% endfor
					</select>
				</td>
				<td><input name="name" /></td>
				<td>
					<select name="type">
% for type in range(0, 3):
						<option value="${type}">${h.event_type_name(type)}</option>
% endfor
					</select>
				</td>
				<td>
					<input type="submit" />
					<input type="reset" value="Abort" onclick="$('addEventRow').hide();" />
				</td>
			</form>
		</tr><tr>
			<td colspan="4">
				&raquo; <a href="" onclick="$('addEventRow').show();return false">Add an event</a>
			</td>
		</tr>
	</tbody>
</table>
