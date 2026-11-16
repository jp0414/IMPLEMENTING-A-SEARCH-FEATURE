<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

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
	<title>Database Verification - Audit Trail</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<div class="top-bar">
		<div class="user-info">
			<span class="user-badge">👤 Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
			<span class="user-name">(<?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>)</span>
		</div>
		<a href="core/handleForms.php?logout=true" class="logout-btn">Logout</a>
	</div>

	<p><a href="index.php">&larr; Return to Home</a></p>

	<h1 style="margin-top: 15px;">Database Verification — Tutor Audit Trail</h1>
	<p style="color: var(--text-muted); margin-bottom: 15px;">This page shows the raw database values for <code>date_added</code>, <code>last_updated</code> (timestamp), and <code>last_updated_by</code> (integer user_id) to verify changes are reflected.</p>

	<h2>Tutors Table (Parent Entity)</h2>
	<table style="width:100%; margin-top: 15px;">
		<tr>
			<th>Tutor ID</th>
			<th>Name</th>
			<th>Specialization</th>
			<th>date_added</th>
			<th>added_by (user_id)</th>
			<th>last_updated_by (user_id)</th>
			<th>last_updated (timestamp)</th>
		</tr>
		<?php
		$tutors = getAllTutors($pdo);
		foreach ($tutors as $t) {
			$sql = "SELECT * FROM tutors WHERE tutor_id = ?";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([$t['tutor_id']]);
			$raw = $stmt->fetch(PDO::FETCH_ASSOC);
		?>
		<tr>
			<td><?php echo htmlspecialchars($raw['tutor_id']); ?></td>
			<td><strong><?php echo htmlspecialchars($raw['first_name'] . ' ' . $raw['last_name']); ?></strong></td>
			<td><?php echo htmlspecialchars($raw['specialization']); ?></td>
			<td><?php echo htmlspecialchars($raw['date_added']); ?></td>
			<td><?php echo htmlspecialchars($raw['added_by']); ?></td>
			<td><strong><?php echo htmlspecialchars($raw['last_updated_by']); ?></strong></td>
			<td><strong><?php echo htmlspecialchars($raw['last_updated']); ?></strong></td>
		</tr>
		<?php } ?>
	</table>

	<h2 style="margin-top: 30px;">Sessions Table (Child Entity)</h2>
	<table style="width:100%; margin-top: 15px;">
		<tr>
			<th>Session ID</th>
			<th>Subject Taught</th>
			<th>Tutor ID</th>
			<th>date_added</th>
			<th>added_by (user_id)</th>
			<th>last_updated_by (user_id)</th>
			<th>last_updated (timestamp)</th>
		</tr>
		<?php
		$sql2 = "SELECT * FROM sessions ORDER BY session_id";
		$stmt2 = $pdo->prepare($sql2);
		$stmt2->execute();
		$sessions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
		foreach ($sessions as $s) {
		?>
		<tr>
			<td><?php echo htmlspecialchars($s['session_id']); ?></td>
			<td><strong><?php echo htmlspecialchars($s['subject_taught']); ?></strong></td>
			<td><?php echo htmlspecialchars($s['tutor_id']); ?></td>
			<td><?php echo htmlspecialchars($s['date_added']); ?></td>
			<td><?php echo htmlspecialchars($s['added_by']); ?></td>
			<td><strong><?php echo htmlspecialchars($s['last_updated_by']); ?></strong></td>
			<td><strong><?php echo htmlspecialchars($s['last_updated']); ?></strong></td>
		</tr>
		<?php } ?>
	</table>
</body>
</html>
