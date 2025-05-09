<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connor Young</title>
  <link rel="stylesheet" type="text/css" href="../resources/css/default_golf.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class='full-page-container'>
  <br>
  <h1 class='text-3xl text-center'>Events (All Tours)</h1>
  <br>

  <?php 
  include('golf_db_connection.php');
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  // Get filters
  $seasonFilter = isset($_GET['season']) ? trim($_GET['season']) : '';
  $tourFilter = isset($_GET['tour']) ? trim($_GET['tour']) : '';
  $courseNameFilter = isset($_GET['course_name']) ? trim($_GET['course_name']) : '';
  $eventNameFilter = isset($_GET['event_name']) ? trim($_GET['event_name']) : '';

  // Pagination
  $limit = 25;
  $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
  $offset = ($page - 1) * $limit;

  // Build filter conditions for reuse
  function buildWhereClause($conn, $season, $course, $event) {
    $clauses = [];
    if ($season !== '') $clauses[] = "season LIKE '%" . mysqli_real_escape_string($conn, $season) . "%'";
    if ($course !== '') $clauses[] = "course_name LIKE '%" . mysqli_real_escape_string($conn, $course) . "%'";
    if ($event !== '')  $clauses[] = "event_name LIKE '%" . mysqli_real_escape_string($conn, $event) . "%'";
    return $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
  }

  $whereClause = buildWhereClause($conn, $seasonFilter, $courseNameFilter, $eventNameFilter);

  // Fetch data from all four tours
  $tours = [
    'PGA' => 'pga_schedule',
    'Euro' => 'euro_schedule',
    'KFT' => 'kft_schedule',
    'LIV' => 'liv_schedule',
  ];

  $allResults = [];

    $queries = [];

    foreach ($tours as $tourName => $tableName) {
    if ($tourFilter === '' || strtolower($tourFilter) === strtolower($tourName)) {
        $queries[] = "SELECT *, '$tourName' AS tour FROM $tableName $whereClause";
    }
    }

    if (!empty($queries)) {
    $unionSql = implode(" UNION ALL ", $queries) . " ORDER BY start_date DESC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $unionSql);
    while ($row = mysqli_fetch_assoc($result)) {
        $allResults[] = $row;
    }
    }


  ?>

  <div class='flex flex-wrap max-w-[85%] mx-auto'>
    <form id='filterForm' method="GET" class='flex flex-wrap max-w-[85%]'>
      <p class='mt-2 mr-5'>Filter by:</p>
      <input type="text" name="season" value="<?= htmlspecialchars($seasonFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Season">
      <input type="text" name="tour" value="<?= htmlspecialchars($tourFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Tour">
      <input type="text" name="course_name" value="<?= htmlspecialchars($courseNameFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Course Name">
      <input type="text" name="event_name" value="<?= htmlspecialchars($eventNameFilter) ?>" class="border rounded px-3 py-2 text-black mr-2" style='border-color: #0D2818' placeholder="Event Name">
    </form>
  </div>

  <br>

  <?php if (!empty($allResults)) : ?>
  <div class='overflow-x-auto'>
    <table class='default-zebra-table text-white w-4/5 mx-auto'>
      <thead>
        <tr>
          <th>Season</th>
          <th>Tour</th>
          <th>Course Name</th>
          <th>Course Key</th>
          <th>Event ID</th>
          <th>Event Name</th>
          <th>Latitude</th>
          <th>Longitude</th>
          <th>Start Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allResults as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['season']) ?></td>
          <td><?= htmlspecialchars($row['tour']) ?></td>
          <td><?= htmlspecialchars($row['course_name']) ?></td>
          <td><?= htmlspecialchars($row['course_key']) ?></td>
          <td><?= htmlspecialchars($row['event_id']) ?></td>
          <td><?= htmlspecialchars($row['event_name']) ?></td>
          <td><?= htmlspecialchars($row['latitude']) ?></td>
          <td><?= htmlspecialchars($row['longitude']) ?></td>
          <td><?= htmlspecialchars($row['start_date']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <br>

  <!-- Pagination (can be refined to include total count across all tours if needed) -->
  <div class='mt-4 text-white flex flex-wrap items-center justify-center gap-2'>
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>" class='px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded'>Prev</a>
    <?php endif; ?>
    <a href="?page=<?= $page ?>" class='px-3 py-1 bg-green-700 font-bold rounded'><?= $page ?></a>
    <a href="?page=<?= $page + 1 ?>" class='px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded'>Next</a>
  </div>
  <br>

  <?php else: ?>
    <p class="text-white text-center">No results found.</p>
  <?php endif; ?>

</div>

<?php 
mysqli_close($conn);
include 'footer.php'; 
?>

<!-- Auto-submit filtering form script -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');
    let typingTimer;
    const doneTypingInterval = 400;

    form.querySelectorAll('input[type="text"]').forEach(input => {
      input.addEventListener('input', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => form.submit(), doneTypingInterval);
      });
    });
  });
</script>

</body>
</html>
