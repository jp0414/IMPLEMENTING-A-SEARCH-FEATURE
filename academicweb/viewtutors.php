<?php // Include database configuration and data models
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

// Authentication Guard: Ensure user is logged in before viewing tutor sessions
if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>View Tutoring Sessions</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Top bar showing active logged-in user profile details and logout button -->
	<div class="top-bar">
		<div class="user-info">
			<span class="user-badge">👤 Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
			<span class="user-name">(<?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>)</span>
		</div>
		<a href="core/handleForms.php?logout=true" class="logout-btn">Logout</a>
	</div>

    <!-- Navigation link back to main tutor directory -->
	<p><a href="index.php">&larr; Return to Home</a></p>

	<?php 
	// Retrieve tutor ID from query parameters and fetch tutor profile
	$tutor_id = $_GET['tutor_id'] ?? null;
	$tutor = getTutorByID($pdo, $tutor_id); 

	// Guard clause: stop execution if tutor does not exist
	if (!$tutor) {
		echo "<h1>Tutor not found!</h1>";
		exit();
	}
	?>

    <!-- Parent tutor overview summary -->
	<h1 style="margin-top: 15px;">Tutor: <?php echo htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']); ?></h1>
	<h3>Specialization: <?php echo htmlspecialchars($tutor['specialization']); ?> | Email: <?php echo htmlspecialchars($tutor['email']); ?> | Rate: $<?php echo htmlspecialchars(number_format($tutor['hourly_rate'], 2)); ?>/hr</h3>

    <!-- Form for logging a new tutoring session under this tutor -->
	<h2 style="margin-top: 30px;">Add New Tutoring Session</h2>
	<form action="core/handleForms.php?tutor_id=<?php echo htmlspecialchars($tutor_id); ?>" method="POST">
		<p>
			<label for="subjectTaught">Subject Taught</label> 
			<input type="text" name="subjectTaught" id="subjectTaught" placeholder="e.g. Calculus I, SAT Math, Physics" required>
		</p>
		<p>
			<label for="sessionDate">Session Date</label> 
			<input type="date" name="sessionDate" id="sessionDate" required>
		</p>
		<p>
			<label for="startTime">Start Time</label> 
			<input type="time" name="startTime" id="startTime" required>
		</p>
		<p>
			<label for="endTime">End Time</label> 
			<input type="time" name="endTime" id="endTime" required>
		</p>
		<p>
			<label for="notes">Session Notes</label> 
			<input type="text" name="notes" id="notes" placeholder="Topics covered, student progress, homework assigned">
		</p>
		<p>
			<input type="submit" name="insertSessionBtn" value="Add Session">
		</p>
	</form>

    <!-- Tutoring sessions data table with audit logs -->
	<h2>Tutoring Sessions List</h2>

	<!-- Search form for finding sessions by subject, notes, or date -->
	<form action="viewtutors.php" method="GET" class="search-form" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
		<input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutor_id); ?>">
		<p style="flex: 1; margin-bottom: 0;">
			<label for="search">Search Sessions</label>
			<input type="text" name="search" id="search" placeholder="Search by subject, notes, or date..." 
				   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="max-width: 100%;">
		</p>
		<p style="margin-bottom: 0;">
			<input type="submit" value="🔍 Search">
		</p>
		<?php if (!empty($_GET['search'])) { ?>
		<p style="margin-bottom: 0;">
			<a href="viewtutors.php?tutor_id=<?php echo htmlspecialchars($tutor_id); ?>" style="display: inline-block; padding: 10px 20px; background: var(--text-muted); color: #fff; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600; text-decoration: none;">✕ Clear</a>
		</p>
		<?php } ?>
	</form>

	<?php 
	// Fetch sessions - if search keyword provided, filter; otherwise get all
	$searchKeyword = $_GET['search'] ?? '';
	if (!empty($searchKeyword)) {
		$sessions = searchSessionsByTutor($pdo, $tutor_id, $searchKeyword);
	} else {
		$sessions = getSessionsByTutor($pdo, $tutor_id);
	}
	?>

	<?php if (!empty($searchKeyword)) { ?>
		<p style="color: var(--text-muted); margin-bottom: 10px;">
			🔍 Search results for "<strong><?php echo htmlspecialchars($searchKeyword); ?></strong>" 
			— <?php echo count($sessions); ?> session(s) found.
		</p>
	<?php } ?>

	<table style="width:100%; margin-top: 20px;">
		<tr>
			<th>Session ID</th>
			<th>Subject Taught</th>
			<th>Date</th>
			<th>Time</th>
			<th>Notes</th>
			<th>Tutor</th>
			<th>Date Added</th>
			<th>Added By</th>
			<th>Last Updated By</th>
			<th>Last Updated</th>
			<th>Action</th>
		</tr>
		<?php 
		if (empty($sessions)) {
		?>
		<tr>
			<td colspan="11" style="text-align: center; color: var(--text-muted);">No sessions found.</td>
		</tr>
		<?php } else { ?>
			<?php 
			// Render each session along with its creator, last editor, and action triggers
			foreach ($sessions as $row) { ?>
			<tr>
				<td><?php echo htmlspecialchars($row['session_id']); ?></td>	  	
				<td><strong><?php echo htmlspecialchars($row['subject_taught']); ?></strong></td>	  	
				<td><?php echo htmlspecialchars($row['session_date']); ?></td>	  	
				<td><?php echo htmlspecialchars(date("g:i A", strtotime($row['start_time'])) . ' - ' . date("g:i A", strtotime($row['end_time']))); ?></td>	  	
				<td><?php echo htmlspecialchars($row['notes'] ?: 'None'); ?></td>
				<td><?php echo htmlspecialchars($row['tutor_name']); ?></td>
				<td><?php echo htmlspecialchars($row['date_added']); ?></td>
				<td><?php echo htmlspecialchars($row['added_by_username'] ?? 'Unknown'); ?></td>
				<td><?php echo htmlspecialchars($row['last_updated_by_username'] ?? $row['added_by_username'] ?? 'Unknown'); ?></td>
				<td><?php echo htmlspecialchars($row['last_updated'] ?? 'N/A'); ?></td>
				<td>
					<a href="editsessions.php?session_id=<?php echo $row['session_id']; ?>&tutor_id=<?php echo $tutor_id; ?>">Edit</a>
					<a href="deletesessions.php?session_id=<?php echo $row['session_id']; ?>&tutor_id=<?php echo $tutor_id; ?>">Delete</a>
				</td>	  	
			</tr>
			<?php } ?>
		<?php } ?>
	</table>
</body>
</html>
