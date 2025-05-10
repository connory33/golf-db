<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../../../favicon.ico">

    <title>Golfer Details</title>

    <link href="../resources/css/default_golf.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
      /* Table styles with more solid borders */
      .stats-table, .historical-rounds-table {
        width: 90% !important;
        max-width: 800px !important;
        margin: 0 auto 20px auto !important;
        border-collapse: separate !important; /* Changed from collapse to separate */
        border-spacing: 0 !important; /* Ensure no gaps between cells */
        border: 2px solid #04471C !important;
        text-align: center !important;
        zoom: .9;
      }
      
      .stats-table th, 
      .stats-table td {
        padding: 10px 15px !important;
        border: 1px solid #1b472b !important; /* Changed to solid color instead of semi-transparent */
        text-align: left !important;
      }
      
      /* Make sure borders don't double up on adjacent cells */
      .stats-table th {
        border-top: none !important;
        border-left: none !important;
        background-color: #1b472b !important; /* Solid color for better visibility */
        color: white !important;
      }
      
      .stats-table th:last-child {
        border-right: none !important;
      }
      
      .stats-table td {
        border-left: none !important;
        border-bottom: 1px solid #1b472b !important;
      }
      
      .stats-table td:last-child {
        border-right: none !important;
      }
      
      /* Last row shouldn't have bottom border */
      .stats-table tr:last-child td {
        border-bottom: none !important;
      }
      
      /* Add better border to category headers */
      .stats-table tr.category-header td {
        background-color: #1b472b !important;
        color: white !important;
        font-weight: bold !important;
        border-bottom: 1px solid #04471C !important;
        border-top: 1px solid #04471C !important;
      }
      
      .stats-category {
        font-weight: bold !important;
      }
      
      .rankings {
        text-align: center !important;
        font-size: 1.2rem !important;
        margin-bottom: 20px !important;
      }
      
      .ranking-value {
        font-weight: bold !important;
      }
      
      .section-header {
        font-weight: bold !important;
        font-size: 1.2rem !important;
        text-align: center !important;
        background-color: #1b472b !important;
        color: white !important;
        padding: 8px !important;
        border-top-left-radius: 5px !important;
        border-top-right-radius: 5px !important;
        margin-bottom: 0 !important; /* Ensure no gap between header and table */
        border: 2px solid #04471C !important;
        border-bottom: none !important;
      }
      
      /* Expandable rows styling */
      .hidden {
        display: none !important;
      }
      .cursor-pointer {
        cursor: pointer;
      }
      .cursor-pointer:hover {
        background-color: rgba(38, 101, 63, 0.2) !important;
      }
      
      /* Style for the nested table to make it stand out */
      .stats-table tr[id^='event-'] td {
        padding: 0 !important;
      }
      
      .stats-table tr[id^='event-'] table {
        margin: 10px;
        border: 1px solid #1b472b;
      }
      
      .stats-table tr[id^='event-'] th {
        background-color: #1b472b !important;
        padding: 8px !important;
      }
      
      /* Expand/collapse icon styling */
      .expand-icon {
        display: inline-block;
        width: 16px;
        text-align: center;
        font-weight: bold;
      }
    </style>
    
    <script>
      // Function to toggle visibility of event details
      function toggleEvent(eventId) {
        console.log('Toggling event:', eventId); // Debug line
        const row = document.getElementById(eventId);
        if (row) {
          const summaryRow = row.previousElementSibling;
          const icon = summaryRow.querySelector('.expand-icon');
          
          if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
            if (icon) icon.textContent = '-';
          } else {
            row.classList.add('hidden');
            if (icon) icon.textContent = '+';
          }
        } else {
          console.error('Could not find element with ID:', eventId);
        }
      }
      
      // Run this when the page loads
      document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, toggle function ready');
      });
    </script>
  </head>
  <body>
     <header>
        <?php include 'header.php'; ?>
</header>
     
    <div class='full-page-container'>
        <br>

        <?php
        include('golf_db_connection.php');

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Description text for each stat category
        $descriptions = [
            'amateur' => 'Indicates whether the golfer has amateur status.',
            'country' => 'The country the golfer represents.',
            'country_code' => 'ISO code for the golfer\'s country.',
            'driving_accuracy' => 'SG based on driving accuracy. Measures driving accuracy compared to the field.',
            'driving_distance' => 'SG based on driving distance. Measures drive distance compared to the field.',
            'sg_approach' => 'Strokes Gained on approach shots. Measures performance on shots approaching the green.',
            'sg_around_green' => 'Strokes Gained around the green. Measures short game performance.',
            'sg_off_the_tee' => 'Strokes Gained off the tee. Measures driving performance.',
            'sg_putting' => 'Strokes Gained putting. Measures performance on the greens.',
            'sg_total' => 'Total Strokes Gained. Overall performance metric versus field average.',
            'datagolf_rank' => 'Current DataGolf ranking position worldwide.',
            'dg_skill_estimate' => 'DataGolf skill estimate - measure of predicted scoring average vs field.',
            'owgr_rank' => 'Official World Golf Ranking position.',
            'primary_tour' => 'The main professional tour the golfer competes on.'
        ];
        
        // Display names for each stat category
        $displayNames = [
            'amateur' => 'Amateur Status',
            'country' => 'Country',
            'country_code' => 'Country Code',
            'driving_accuracy' => 'Driving Accuracy',
            'driving_distance' => 'Driving Distance',
            'sg_approach' => 'SG: Approach',
            'sg_around_green' => 'SG: Around Green',
            'sg_off_the_tee' => 'SG: Off the Tee',
            'sg_putting' => 'SG: Putting',
            'sg_total' => 'SG: Total',
            'datagolf_rank' => 'DataGolf Rank',
            'dg_skill_estimate' => 'DG Skill Estimate',
            'owgr_rank' => 'OWGR Rank',
            'primary_tour' => 'Primary Tour'
        ];

        // Check if the 'game_id' is passed in the URL
        if (isset($_GET['dg_id'])) {
            $dg_id = $_GET['dg_id'];

            $sql = "SELECT * FROM players_w_stats_ranks WHERE dg_id = $dg_id";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $name = $row['firstName'] . " " . $row['lastName'];
                $stats_last_update = $row['last_updated_stats'];
                $ranks_last_update = $row['last_updated_estimate_ranks'];
                
                // Format ranking numbers
                $owgrRank = !empty($row['owgr_rank']) ? number_format($row['owgr_rank']) : 'N/A';
                $dgRank = !empty($row['datagolf_rank']) ? number_format($row['datagolf_rank']) : 'N/A';
                $dgSkill = !empty($row['dg_skill_estimate']) ? number_format($row['dg_skill_estimate'], 2) : 'N/A';
                
                // Apply +/- to skill estimate
                if (is_numeric($row['dg_skill_estimate'])) {
                    $skillClass = floatval($row['dg_skill_estimate']) > 0 ? 'text-green-500' : 'text-red-500';
                    $skillPrefix = floatval($row['dg_skill_estimate']) > 0 ? '+' : '';
                    $dgSkill = "<span class='$skillClass'>$skillPrefix$dgSkill</span>";
                }
                
                echo "<div class='text-center mb-4'>";
                echo "<h1 class='text-3xl mb-2'>Golfer Details: " . htmlspecialchars($name) . " <span class='text-gray-400'>(" . htmlspecialchars($dg_id) . ")</span></h1>";
                echo "<div class='rankings'>";
                echo "OWGR: <span class='ranking-value'>#$owgrRank</span> | ";
                echo "DG: <span class='ranking-value'>#$dgRank</span> (Skill Estimate: <span class='ranking-value'>$dgSkill</span>)<br>";
                if ($row['amateur'] == 1) {
                    echo "<span>Amateur Status: True</span> | ";
                } else {
                    echo "<span>Amateur Status: False</span> | ";
                }
                $country = htmlspecialchars($row['country']);
                if ($country == 'Korea - Republic of') {
                    $country = 'Republic of Korea';
                }
                echo "Country: <span>" . htmlspecialchars($country) . "</span> | ";
                echo "Primary Tour: <span>" . htmlspecialchars($row['primary_tour']) . "</span>";
                echo "</div>";
                
                echo "<p class='text-sm'>Stats Last Update: " . htmlspecialchars($stats_last_update) . "</p>";
                echo "<p class='text-sm mb-4'>Ranks Last Update: " . htmlspecialchars($ranks_last_update) . "</p>";
                echo "</div>";
                
                // Performance Stats Table
                echo "<table class='stats-table'>";
                echo "<tbody>";
                
                // Group fields by type - excluded personal info and rankings which are displayed separately
                $statFields = [
                    'Current Strokes Gained Metrics' => ['driving_accuracy', 'driving_distance','sg_off_the_tee', 'sg_approach', 'sg_around_green', 'sg_putting', 'sg_total']
                ];
                
                foreach ($statFields as $groupName => $fields) {
                    echo "<tr class='category-header'>";
                    echo "<td colspan='3'>{$groupName}</td>";
                    echo "</tr>";
                    
                    foreach ($fields as $field) {
                        echo "<tr>";
                        echo "<td class='stats-category'>" . htmlspecialchars($displayNames[$field]) . "</td>";
                        
                        // Format specific value types
                        $value = $row[$field];
                        if (in_array($field, ['driving_accuracy', 'driving_distance', 'sg_approach', 'sg_around_green', 'sg_off_the_tee', 'sg_putting', 'sg_total'])) {
                            // Format strokes gained with 2 decimal places and +/- sign
                            $formattedValue = number_format(floatval($value), 2);
                            if (floatval($value) > 0) {
                                $value = "<span class='text-green-700'>+{$formattedValue}</span>";
                            } else {
                                $value = "<span class='text-red-700'>{$formattedValue}</span>";
                            }
                            echo "<td>" . $value . "</td>";
                        } else {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        
                        echo "<td>" . htmlspecialchars($descriptions[$field]) . "</td>";
                        echo "</tr>";
                    }
                }
                
                echo "</tbody></table>";
                echo "<div class='text-center mx-auto max-w-[800px]'>";
                echo "<p class='text-sm font-bold'>What is Strokes Gained?</p>";
                echo "<p class='text-sm'>Strokes Gained is a statistical measure that compares a player's performance to the field average. A positive value indicates better performance than average, while a negative value indicates below-average performance.
                For example, a value of +1.0 for SG Off the Tee means that the player's performance off the tee gains them 1 shot over the average player over the course of a round.</p>";
                echo "</div>";
                
                echo "<br>";

                // Historical Rounds //
                $sql = "SELECT all_historical_rounds.*, historical_tournaments.event_name
                        FROM all_historical_rounds 
                        LEFT JOIN historical_tournaments ON all_historical_rounds.event_id = historical_tournaments.event_id
                        WHERE dg_id = $dg_id ORDER BY event_completed_date DESC;";
                $result = mysqli_query($conn, $sql);
                
                echo "<div class='text-center mb-4'>";
                echo "<table class='stats-table'>";
                echo "<thead>";
                echo "<tr class='section-header'>";
                echo "<th class='text-center' colspan='16'>Past Events - click any row to see individual rounds.</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<th>Event</th>";
                echo "<th>End Date</th>";
                echo "<th>Finish</th>";
                echo "<th>Total Score</th>";
                echo "<th>Course Par</th>";
                echo "<th>To Par</th>";
                echo "<th>Birdies</th>";
                echo "<th>Bogies</th>";
                echo "<th>Driving Acc.</th>";
                echo "<th>Driving Dist.</th>";
                echo "<th>GIR</th>";
                echo "<th>SG: App.</th>";
                echo "<th>SG: ArG.</th>";
                echo "<th>SG: OTT</th>";
                echo "<th>SG: Putt.</th>";
                echo "<th>SG: Total</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                
                // Process the result set to group rounds by event
                $events = [];
                $processed_event_ids = []; // Track processed event IDs

                while ($row = mysqli_fetch_assoc($result)) {
                    $event_id = $row['event_id'];
                    $course_par = $row['course_par'];
                    
                    // Only process this event if we haven't seen it before
                    if (!in_array($event_id, $processed_event_ids)) {
                        $processed_event_ids[] = $event_id; // Mark as processed
                        
                        // Initialize event
                        $events[$event_id] = [
                            'id' => $event_id,
                            'name' => $row['event_name'],
                            'end_date' => $row['event_completed_date'],
                            'fin_text' => $row['fin_text'],
                            'rounds' => []
                        ];
                        
                        // Get all rounds for this event in a separate query to avoid duplicates
                        $rounds_sql = "SELECT * FROM all_historical_rounds 
                                    WHERE dg_id = $dg_id AND event_id = $event_id
                                    ORDER BY event_round ASC";
                                    
                        $rounds_result = mysqli_query($conn, $rounds_sql);
                        
                        while ($round = mysqli_fetch_assoc($rounds_result)) {
                            $events[$event_id]['rounds'][] = $round;
                        }
                    }
                }
                
                // Now display the grouped data with expandable rows
                foreach ($events as $event_id => $event) {
                    // Format the date
                    $event_date = date('m/d/Y', strtotime($event['end_date']));
                    
                    // Calculate summary metrics
                    $total_score = 0;
                    $total_to_par = 0;
                    $total_birdies = 0;
                    $total_bogies = 0;
                    $total_driving_accuracy = 0;
                    $total_driving_distance = 0;
                    $total_gir = 0;
                    $total_sg_app = 0;
                    $total_sg_arg = 0;
                    $total_sg_ott = 0;
                    $total_sg_putt = 0;
                    $total_sg_total = 0;
                    
                    foreach ($event['rounds'] as $round) {
                        $total_score += $round['score'];
                        $total_to_par += $round['toPar'];
                        $total_birdies += $round['birdies'];
                        $total_bogies += $round['bogies'];
                        $total_driving_accuracy += (float)$round['driving_accuracy']/count($event['rounds']);
                        $total_driving_distance += (float)$round['driving_distance']/count($event['rounds']);
                        $total_gir += (float)$round['gir']/count($event['rounds']);
                        $total_sg_app += (float)$round['sg_app'];
                        $total_sg_arg += (float)$round['sg_arg'];
                        $total_sg_ott += (float)$round['sg_ott'];
                        $total_sg_putt += (float)$round['sg_putt'];
                        $total_sg_total += (float)$round['sg_total'];
                    }
                    
                    // Display the summary row (clickable to expand)
                    echo "<tr class='cursor-pointer hover:bg-green-900/20' onclick=\"toggleEvent('event-{$event_id}')\">";
                    echo "<td><span class='expand-icon mr-2'>+</span>" . htmlspecialchars($event['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($event_date) . "</td>";
                    echo "<td>" . htmlspecialchars($event['fin_text']) . "</td>";
                    echo "<td>" . $total_score . "</td>";
                    echo "<td>" . $course_par . "</td>";
                    echo "<td>" . $total_to_par . "</td>";
                    echo "<td>" . $total_birdies . "</td>";
                    echo "<td>" . $total_bogies . "</td>";
                    echo "<td>" . number_format($total_driving_accuracy * 100, 1) . "%</td>";
                    echo "<td>" . number_format($total_driving_distance, 1) . " yds</td>";
                    echo "<td>" . number_format($total_gir * 100, 1) . "%</td>";
                    
                    // Format SG values with color coding
                    $sg_metrics = [
                        'app' => $total_sg_app,
                        'arg' => $total_sg_arg,
                        'ott' => $total_sg_ott,
                        'putt' => $total_sg_putt,
                        'total' => $total_sg_total
                    ];
                    
                    foreach ($sg_metrics as $metric => $value) {
                        $formatted = number_format($value, 2);
                        $class = ($value >= 0) ? 'text-green-700' : 'text-red-700';
                        $prefix = ($value >= 0) ? '+' : '';
                        echo "<td class='$class'>" . $prefix . $formatted . "</td>";
                    }
                    
                    echo "</tr>";
                    
                    // Hidden details row for all rounds in this event
                    echo "<tr id='event-{$event_id}' class='hidden'>";
                    echo "<td colspan='16'>";
                    echo "<div class='p-2'>";
                    echo "<table class='w-full text-sm'>";
                    echo "<thead><tr class='bg-green-800 text-white'>";
                    echo "<th>Round</th>";
                    echo "<th>Score</th>";
                    echo "<th>To Par</th>";
                    echo "<th>Birdies</th>";
                    echo "<th>Bogies</th>";
                    echo "<th>Course Par</th>";
                    echo "<th>Driving Acc.</th>";
                    echo "<th>Driving Dist.</th>";
                    echo "<th>GIR</th>";
                    echo "<th>SG: App.</th>";
                    echo "<th>SG: ArG.</th>";
                    echo "<th>SG: OTT</th>";
                    echo "<th>SG: Putt.</th>";
                    echo "<th>SG: Total</th>";
                    echo "</tr></thead>";
                    echo "<tbody>";
                    
                    // Sort rounds by round number
                    usort($event['rounds'], function($a, $b) {
                        return $a['event_round'] - $b['event_round'];
                    });
                    
                    foreach ($event['rounds'] as $round) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($round['event_round']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['score']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['toPar']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['birdies']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['bogies']) . "</td>";
                        echo "<td>" . htmlspecialchars($round['course_par']) . "</td>";
                        
                        // Format driving accuracy as percentage, average over rounds
                        $driving_accuracy = number_format(floatval($round['driving_accuracy']) * 100, 1) . '%';
                        echo "<td>" . $driving_accuracy . "</td>";
                        
                        // Format driving distance, average over rounds
                        $driving_distance = number_format(floatval($round['driving_distance']), 1) . ' yds';
                        echo "<td>" . $driving_distance . "</td>";
                        
                        // Format GIR as percentage, average over rounds
                        
                        $gir = number_format(floatval($round['gir']) * 100, 1) . '%';
                        echo "<td>" . $gir . "</td>";
                        
                        // Format SG metrics with color-coding
                        $sg_metrics = ['sg_app', 'sg_arg', 'sg_ott', 'sg_putt', 'sg_total'];
                        foreach ($sg_metrics as $metric) {
                            $value = $round[$metric];
                            $formatted = number_format(floatval($value), 2);
                            $class = (floatval($value) >= 0) ? 'text-green-700' : 'text-red-700';
                            $prefix = (floatval($value) >= 0) ? '+' : '';
                            echo "<td class='$class'>" . $prefix . $formatted . "</td>";
                        }
                        
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table></div></td></tr>";
                }
                
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                
                mysqli_close($conn);
            } else {
                echo "<div class='text-center text-xl'>";
                echo "No results found for golfer ID: " . htmlspecialchars($dg_id);
                echo "</div>";
            }
        } else {
            echo "<div class='text-center text-xl'>";
            echo "No golfer ID provided.";
            echo "</div>";
        }
        ?>
    </div>
    <?php include 'footer.php'; ?>
  </body>
</html>