<?php
// Include website settings
require_once 'includes/website_settings.php';
require_once 'auth/db_connect.php';
require_once 'includes/layout_functions.php';

// SEO Meta (Defaults)
$PAGE_TITLE = "Browse Escorts by City & Area | ADDAAX";
$META_DESC = "Find escorts and call girl services by city and area. Browse verified listings.";

renderHeader($PAGE_TITLE, 'cities');
?>

    <style>
        .city-group {
            margin-bottom: 50px;
            padding: 0 10px;
        }
        
        .city-name {
            font-size: 28px;
            font-weight: 800;
            color: var(--white);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .city-name::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--glass-border);
        }
        
        .pills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .area-pill {
            background: rgba(245, 233, 200, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-main);
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .area-pill:hover {
            background: var(--accent-gold);
            color: var(--dark-purple);
            border-color: var(--accent-gold);
            transform: translateY(-2px);
        }

        .area-pill.popular {
            background: rgba(201, 168, 76, 0.1);
            border-color: rgba(201, 168, 76, 0.3);
        }
        
        .page-intro {
            padding: 40px 0 60px;
        }
        
        .page-intro h1 {
            font-size: 42px;
            font-weight: 900;
            color: var(--accent-gold);
            margin-bottom: 10px;
        }

        .see-more-container {
            margin-top: 20px;
            display: flex;
            justify-content: flex-start;
        }

        .btn-see-more {
            background: transparent;
            border: 1px solid var(--accent-gold);
            color: var(--accent-gold);
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-see-more:hover {
            background: var(--accent-gold);
            color: var(--dark-purple);
        }

        .city-item.hidden {
            display: none;
        }
    </style>

    <main class="container-wide page-header-offset">
        <div class="page-intro">
            <h1>Browse by Location</h1>
            <p style="color: var(--text-muted);">Explore premium verified listings across Pakistan grouped by provinces and major cities.</p>
        </div>

        <div style="padding-bottom: 80px;">
            <?php
            // Fetch States/Provinces
            $state_sql = "SELECT * FROM states WHERE status = 1 ORDER BY name ASC";
            $state_res = $conn->query($state_sql);
            
            if ($state_res && $state_res->num_rows > 0):
                while ($state = $state_res->fetch_assoc()):
                    $state_id = $state['id'];
                    $state_name = $state['name'];
            ?>
                <div class="city-group">
                    <h2 class="city-name"><?php echo htmlspecialchars($state_name); ?></h2>
                    
                    <div class="pills-container" id="state-<?php echo $state_id; ?>">
                        <?php
                        // Fetch Cities for this State
                        $city_sql = "SELECT * FROM cities WHERE state_id = $state_id AND status = 1 ORDER BY name ASC";
                        $city_res = $conn->query($city_sql);
                        
                        if ($city_res && $city_res->num_rows > 0):
                            $city_count = 0;
                            while ($city = $city_res->fetch_assoc()):
                                $city_count++;
                                $city_slug = str_replace(' ', '-', strtolower($city['name']));
                                $isHidden = $city_count > 10 ? 'hidden' : '';
                        ?>
                            <a href="/escorts/<?php echo $city_slug; ?>" class="area-pill city-item <?php echo $isHidden; ?>">
                                <?php echo htmlspecialchars($city['name']); ?>
                            </a>
                        <?php 
                            endwhile;
                            
                            if ($city_count > 10):
                        ?>
                            <div class="see-more-container">
                                <button type="button" class="btn-see-more" onclick="showMoreCities(<?php echo $state_id; ?>, this)">
                                    <i class="fas fa-plus"></i> See more
                                </button>
                            </div>
                        <?php
                            endif;
                        else:
                            echo '<span style="color: var(--text-muted); font-size: 14px;">No cities added yet.</span>';
                        endif; 
                        ?>
                    </div>
                </div>
            <?php 
                endwhile;
            else:
                echo '<div style="text-align:center; padding: 100px; color: var(--text-muted);">No provinces found.</div>';
            endif; 
            ?>
        </div>
    </main>

<script>
function showMoreCities(stateId, button) {
    const container = document.getElementById('state-' + stateId);
    const hiddenCities = container.querySelectorAll('.city-item.hidden');
    
    // Show next 10 hidden cities
    for (let i = 0; i < 10 && i < hiddenCities.length; i++) {
        hiddenCities[i].classList.remove('hidden');
    }
    
    // Hide button if no more hidden cities
    if (container.querySelectorAll('.city-item.hidden').length === 0) {
        button.parentElement.style.display = 'none';
    }
}
</script>

<?php 
renderFooter();
?>
