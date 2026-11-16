<?php // Include database connection and query models
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

// AUTH GUARD: Redirect to login page if user is NOT logged in
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
	<title>Academic Web Tutoring Management System</title>
	<link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Top bar showing logged-in user info and logout action -->
	<div class="top-bar">
		<div class="user-info">
			<span class="user-badge">👤 Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
			<span class="user-name">(<?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>)</span>
		</div>
		<a href="core/handleForms.php?logout=true" class="logout-btn">Logout</a>
	</div>

	<h1>Welcome To Academic Web Tutoring Management System. Add New Tutor!</h1>

    <!-- Form for registering a new tutor -->
	<form action="core/handleForms.php" method="POST">
		<p>
			<label for="firstName">First Name</label>
			<input type="text" name="firstName" id="firstName" required>
		</p>
		<p>
			<label for="lastName">Last Name</label>
			<input type="text" name="lastName" id="lastName" required>
		</p>
		<p>
			<label for="email">Email</label>
			<input type="email" name="email" id="email" required>
		</p>
		<p>
			<label for="phoneNumber">Phone Number</label>
			<input type="text" name="phoneNumber" id="phoneNumber">
		</p>
		<p>
			<label for="specialization">Specialization</label>
			<input type="text" name="specialization" id="specialization" placeholder="e.g. Math, Physics, Web Dev"
				required>
		</p>
		<p>
			<label for="hourlyRate">Hourly Rate (PHP)</label>
			<input type="number" step="0.01" name="hourlyRate" id="hourlyRate" placeholder="e.g. 50.00" required>
		</p>
		<p>
			<input type="submit" name="insertTutorBtn" value="Add Tutor">
		</p>
	</form>

	<h2>All Registered Tutors</h2>

	<!-- Search form for finding tutors by name, email, or specialization -->
	<form action="index.php" method="GET" class="search-form" style="display: flex; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
		<p style="flex: 1; margin-bottom: 0;">
			<label for="search">Search Tutors</label>
			<input type="text" name="search" id="search" placeholder="Search by name, email, specialization..." 
				   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="max-width: 100%;">
		</p>
		<p style="margin-bottom: 0;">
			<input type="submit" value="🔍 Search">
		</p>
		<?php if (!empty($_GET['search'])) { ?>
		<p style="margin-bottom: 0;">
			<a href="index.php" style="display: inline-block; padding: 10px 20px; background: var(--text-muted); color: #fff; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600; text-decoration: none;">✕ Clear</a>
		</p>
		<?php } ?>
	</form>

	<?php 
    // Fetch tutors - if search keyword provided, filter; otherwise get all
    $searchKeyword = $_GET['search'] ?? '';
    if (!empty($searchKeyword)) {
        $getAllTutors = searchTutors($pdo, $searchKeyword);
    } else {
        $getAllTutors = getAllTutors($pdo);
    }
    ?>

    <?php if (!empty($searchKeyword)) { ?>
        <p style="color: var(--text-muted); margin-bottom: 10px;">
            🔍 Search results for "<strong><?php echo htmlspecialchars($searchKeyword); ?></strong>" 
            — <?php echo count($getAllTutors); ?> tutor(s) found.
        </p>
    <?php } ?>

	<?php if (empty($getAllTutors)) { ?>
		<p style="color: var(--text-muted); margin-top: 15px;">No tutors found.</p>
	<?php } ?>

	<?php 
    // Loop through each tutor and render information card
    foreach ($getAllTutors as $row) { ?>
		<div class="container">
			<h3>Tutor Name: <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h3>
			<h3>Email: <?php echo htmlspecialchars($row['email']); ?></h3>
			<h3>Phone: <?php echo htmlspecialchars($row['phone_number'] ?: 'N/A'); ?></h3>
			<h3>Specialization: <?php echo htmlspecialchars($row['specialization']); ?></h3>
			<h3>Hourly Rate: $<?php echo htmlspecialchars(number_format($row['hourly_rate'], 2)); ?></h3>
			<h3>Date Joined: <?php echo htmlspecialchars($row['date_joined']); ?></h3>
			<h3>Date Added: <?php echo htmlspecialchars($row['date_added']); ?></h3>

            <!-- Audit Trail: shows creator, last editor, and timestamp -->
			<div class="meta-info">
				<span>📝 Added by: <strong><?php echo htmlspecialchars($row['added_by_username'] ?? 'Unknown'); ?></strong></span>
				<span>✏️ Last Updated by: <strong><?php echo htmlspecialchars($row['last_updated_by_username'] ?? $row['added_by_username'] ?? 'Unknown'); ?></strong></span>
				<span>🕐 Last Updated: <strong><?php echo htmlspecialchars($row['last_updated'] ?? 'N/A'); ?></strong></span>
			</div>

            <!-- Tutor Actions: view associated sessions, edit details, or delete -->
			<div class="editAndDelete">
				<a href="viewtutors.php?tutor_id=<?php echo $row['tutor_id']; ?>">View Sessions</a>
				<a href="edittutors.php?tutor_id=<?php echo $row['tutor_id']; ?>">Edit</a>
				<a href="deletetutors.php?tutor_id=<?php echo $row['tutor_id']; ?>">Delete</a>
			</div>
		</div>
	<?php } ?>
</body>

</html>