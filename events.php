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

  function getLocationFromCoords($latitude, $longitude) {
                // Make sure to set a unique user agent as required by OSM's usage policy
                $userAgent = 'YourApp/1.0 (your@email.com)';
                
                $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                $result = json_decode($response, true);
                
                if (isset($result['address'])) {
                    $address = $result['address'];
                    
                    // Extract location components
                    $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['hamlet'] ?? '';
                    $state = $address['state'] ?? $address['county'] ?? '';
                    $country = $address['country'] ?? '';
                    
                    return [
                        'city' => $city,
                        'state' => $state,
                        'country' => $country,
                        'full_address' => $result['display_name'] ?? '',
                        'raw_data' => $address
                    ];
                }
                
                return null;
            }

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

  $allResults = [];


    $sql = "SELECT * FROM all_tours_events $whereClause ORDER BY start_date DESC LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $allResults[] = $row;
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
          <!-- <th>Course Key</th> -->
          <!-- <th>Event ID</th> -->
          <th>Event Name</th>
          <th>Location</th>
          <!-- <th>Longitude</th> -->
          <th>Start Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allResults as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['season']) ?></td>
          <?php
          if ($row['tour'] == 'pga') {
          $row['tour'] = 'PGA';
        } elseif ($row['tour'] == 'euro') {
          $row['tour'] = 'European';
        } elseif ($row['tour'] == 'kft') {
          $row['tour'] = 'KFT';
        } elseif ($row['tour'] == 'liv') {
          $row['tour'] = 'LIV';
        }
        ?>
          <td><?= htmlspecialchars($row['tour']) ?></td>
          <td><?= htmlspecialchars($row['course_name']) ?></td>
          <td><a href="https://connoryoung.com/event.php?event_id=<?= htmlspecialchars($row['event_id']) ?>" class='text-blue-700'><?= htmlspecialchars($row['event_name']) ?></a></td>
          <!-- Convert latitude and longitude to city, state/country -->
            <?php
            // $latitude = htmlspecialchars($row['latitude']);
            // $longitude = htmlspecialchars($row['longitude']);
            $lat = $row['latitude'];
            $long = $row['longitude'];
            $location = getLocationFromCoords($lat, $long);
            if ($location) {
                if ($location['city'] AND $location['state'] AND $location['country']) {
                echo "<td>" . $location['city'] . ", " . $location['state'] . " (" . $location['country'] . ")</td>";
                } elseif ($location['state'] AND $location['country']) {
                echo "<td>" . $location['state'] . " (" . $location['country'] . ")</td>";
                } elseif ($location['city'] AND $location['country']) {
                echo "<td>" . $location['city'] . " (" . $location['country'] . ")</td>";
                } else {
                echo "<td>Unknown</td>";
                }
            } else {
                echo "<td>Unknown</td>";
            }
            ?>
          <td><?= htmlspecialchars(date('m/d/Y', strtotime($row['start_date']))) ?></td>
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
