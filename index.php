<?php
session_start();

if(!isset($_SESSION['access_token'])) {
	header('Location: google-login.php');
	exit();
}
// include the google calendar api
require_once('google-calendar-api.php');
$capi = new GoogleCalendarApi();
?>
<!DOCTYPE html>
<html>
<head>
	<title>
		Google Calendar Client App
	</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.1.9/jquery.datetimepicker.min.css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.1.9/jquery.datetimepicker.min.js"></script>
</head>

<body>

	<!--  app title -->
	<div>
		<h1>Google Calendar Events</h1>
	</div>

	<!-- add event btn -->
	<div>
		<a href="add-event.php">Add Event</a>
	</div>

	<!-- list of calendar -->
	<div>
		<b>Calendar List</b>
		<?php
		$eventsList = $capi -> GetCalendarsList($_SESSION['access_token']);
		if (!empty($eventsList)) {
			?>
			<ol>
				<?php
				foreach ($eventsList as $eventObj) {
					?>
					<li><?php print_r($eventObj); ?></li>
					<?php
				}
				?>
			</ol>
			<?php
		} else {
			?>
			<p>no calendar found.</p>
			<?php
		}
		?>
	</div>

	<!-- list of events -->
	<div>
		<b>Events List</b>
		<form action="" method="post">
		<select name="calendar" required>
			<?php
			$calendarsList = $capi -> GetCalendarsList($_SESSION['access_token']);
			if (!empty($calendarsList)) {
			  ?>
			  <option value="" hidden>
					Select Calendar
				</option>
			  <?php
			  foreach ($calendarsList as $calendarObj) {
			  ?>
			  <option value="<?php echo $calendarObj['id']; ?>">
					<?php echo $calendarObj['summary']; ?>
				</option>
			  <?php
			  }
			} else {
			  ?>
			  <option value="" hidden>
					No Calendars Found
				</option>
			  <?php
			}
			?>
		</select>
			<button type="submit" name="showEventsList">
				Show Events List
			</button>
			<br>
		</form>
		<?php
		if (isset($_POST['showEventsList'])) {
			$calendarID = $_POST['calendar'];
			?>
			<br>
			Selected Calendar:
			<b>
			<?php echo $calendarID; ?>
			</b>
			<?php
			$eventsList = $capi -> GetEventsList($_SESSION['access_token'], $calendarID);
			if (!empty($eventsList)) {
				foreach ($eventsList as $eventObj) {
					if (is_array($eventObj) && sizeof($eventObj) >= 2) {
						?>
						<ol>
							<?php
							foreach ($eventObj as $eventObj2) {
								// start time
								$start = $eventObj2['start'];
								$start_time = '';
								if (isset($start['date'])) {
									$startDate = $start['date'];
									$start_time .= " ".$startDate;
								}
								if (isset($start['dateTime'])) {
									$startDateTime = $start['dateTime'];
									$start_time .= $startDateTime;
								}
								// end time
								$end = $eventObj2['end'];
								$end_time = '';
								if (isset($end['date'])) {
									$endDate = $end['date'];
									$end_time .= $endDate;
								}
								if (isset($end['dateTime'])) {
									$endDateTime = $end['dateTime'];
									$end_time .= $endDateTime;
								}
								$event_date = "";
								if (!strpos($start_time, " ")) {
									$event_date = $start_time;
								}
								// print_r($eventObj2);
								?>
								<li id="<?php echo $eventObj2['id']; ?>">
									<?php
									print_r($eventObj2['summary']);
									?>
									&nbsp;
									<a href="edit-event.php?calendar=<?php echo $calendarID; ?>&event=<?php echo $eventObj2['id']; ?>&summary=<?php echo $eventObj2['summary']; ?>&start_time=<?php echo $start_time; ?>&end_time=<?php echo $end_time; ?>&event_date=<?php echo $start_time; ?>">
										Edit
									</a> | 
									 <form>
									 <input type="hidden" id="calendar" value="<?php echo $calendarID; ?>">
									 <input type="hidden" id="event-id" value="<?php echo $eventObj2['id']; ?>">
									 <a href="#" id="delete-event">Delete</a>
									 </form>
									</div>
								</li>
								<?php
							}
							?>
						</ol>
						<?php
					}
				}
			} else {
				?>
				<p>no event found.</p>
				<?php
			}
		}
		?>
	</div>

<script type="text/javascript">

// Send an ajax request to delete event
$("#delete-event").on('click', function(e) {
	if (confirm('Are you sure to delete?')) {
	// Event details
	var parameters = {
operation: 'delete',
event_id: $("#event-id").val()
};
calendarId = $("#calendar").val();

	$("#create-update-event").attr('disabled', 'disabled');
	$("#delete-event").attr('disabled', 'disabled');
	$.ajax({
        type: 'POST',
        url: 'ajax.php',
        data: { event_details: parameters, calendar: calendarId },
        dataType: 'json',
        success: function(response) {
        	$("#"+parameters.event_id).removeAttr('disabled').hide();

					// $("#form-container input").val('');
        	// $("#create-update-event").removeAttr('disabled');

        	$("#create-update-event").text('Create Event').attr('data-event-id', '').attr('data-operation', 'create');

        	alert('Event ID ' + parameters.event_id + ' deleted');

        },
        error: function(response) {
            $("#delete-event").removeAttr('disabled');
            alert(response.responseJSON.message);
        }
    });

	}
});

</script>

</body>
</html>
