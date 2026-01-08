<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

function sitemap_config() {
    return [
        "name" => "Sitemap Generator",
        "description" => "Generates dated XML sitemap including Products and Knowledgebase with cron and management options.",
        "version" => "1.3",
        "author" => "Khalequzzaman",
        "fields" => []
    ];
}

function sitemap_activate() {
    return ["status" => "success", "description" => "Activated successfully."];
}

function sitemap_deactivate() {
    return ["status" => "success", "description" => "Deactivated successfully."];
}

function sitemap_output($vars) {
    $moduleDir = dirname(__FILE__);
    $files = glob($moduleDir . "/sitemap_*.xml");

    // Sort files by modified time descending (newest first)
    if ($files) {
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
    }

    // Handle Remove Sitemap Request (single)
    if (isset($_POST['remove']) && isset($_POST['filename'])) {
        $targetFile = $moduleDir . DIRECTORY_SEPARATOR . basename($_POST['filename']);
        if (file_exists($targetFile)) {
            unlink($targetFile);
            echo '<p style="color: red;">Deleted sitemap: ' . basename($targetFile) . '</p>';
        }
        $files = glob($moduleDir . "/sitemap_*.xml");
        if ($files) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
        }
    }

    // Handle Remove All Sitemaps Request
    if (isset($_POST['remove_all'])) {
        foreach (glob($moduleDir . "/sitemap_*.xml") as $file) {
            @unlink($file);
        }
        echo '<p style="color: red;">All sitemap files have been deleted.</p>';
        $files = [];
    }

    // Handle Generate Sitemap Request
    if (isset($_POST['generate'])) {
        $result = sitemap_generate($moduleDir);
        echo "<p style='color: green;'>$result</p>";
        $files = glob($moduleDir . "/sitemap_*.xml");
        if ($files) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
        }
    }

    // --- Controls ---
    echo '<div style="margin-bottom:15px;display:flex;flex-wrap:wrap;gap:10px;">';
    echo '<form method="post">
        <input type="submit" name="generate" value="Generate Sitemap" class="btn btn-primary" />
    </form>';

    if ($files && count($files) > 1) {
        echo '<form method="post">
            <input type="submit" name="remove_all" value="Delete All Sitemaps" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete ALL sitemaps?\');" />
        </form>';
    }
    echo '</div>';

    // --- Sitemaps List ---
    if ($files) {
        echo "<h3>Available Sitemaps</h3>";

        echo '<style>
        .sitemap-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sitemap-table th, .sitemap-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        .sitemap-table th {
            background: #f5f5f5;
        }
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sitemap-table thead {
                display: none;
            }
            .sitemap-table, .sitemap-table tbody, .sitemap-table tr, .sitemap-table td {
                display: block;
                width: 100%;
            }
            .sitemap-table tr {
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 6px;
                padding: 10px;
                background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .sitemap-table td {
                border: none;
                padding: 8px 5px;
                position: relative;
            }
            .sitemap-table td::before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                margin-bottom: 3px;
                color: #444;
            }
        }
        </style>';

        echo '<table class="sitemap-table">';
        echo '<thead><tr>
                <th>Filename</th>
                <th>Size (KB)</th>
                <th>Actions</th>
              </tr></thead><tbody>';

        foreach ($files as $index => $filePath) {
            $fileSizeKB = round(filesize($filePath) / 1024, 2);
            $filename = basename($filePath);
            $isLatest = $index === 0;

            echo "<tr>";
            echo "<td data-label='Filename'>" . ($isLatest ? "<strong>Latest:</strong> " : "") . htmlspecialchars($filename) . "</td>";
            echo "<td data-label='Size'>{$fileSizeKB}</td>";
            echo "<td data-label='Actions'>
                <a href='/modules/addons/sitemap/$filename' target='_blank' class='btn btn-success btn-sm'>View</a>
                <a href='/modules/addons/sitemap/$filename' download class='btn btn-info btn-sm'>Download</a>
                <form method='post' style='display:inline;'>
                    <input type='hidden' name='filename' value='" . htmlspecialchars($filename) . "' />
                    <input type='submit' name='remove' value='Delete' class='btn btn-danger btn-sm' onclick='return confirm(\"Delete " . htmlspecialchars($filename) . "?\");' />
                </form>
            </td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo '<p><em>No sitemap file currently exists.</em></p>';
    }

    // --- Footer ---
    $config = sitemap_config();
    echo '<hr>';
    echo '<p style="font-size:12px;color:#666;text-align:center;">' 
         . htmlspecialchars($config['name']) 
         . ' • Developed ❤️ by ' . htmlspecialchars($config['author']) 
         . ' • v' . htmlspecialchars($config['version']) 
         . '</p>';
}

function sitemap_generate($moduleDir) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $baseUrl = $protocol . rtrim($_SERVER['HTTP_HOST'], '/') . '/';
    $urls = [];

    // Homepage
    $urls[] = ['loc' => $baseUrl, 'priority' => '1.0'];

    // Product Groups
    $groups = Capsule::table('tblproductgroups')->get();
    foreach ($groups as $group) {
        $urls[] = [
            'loc' => $baseUrl . 'cart.php?gid=' . $group->id,
            'priority' => '0.9'
        ];
    }

    // Products
    $products = Capsule::table('tblproducts')->get();
    foreach ($products as $product) {
        $urls[] = [
            'loc' => $baseUrl . 'cart.php?a=add&pid=' . $product->id,
            'priority' => '0.8'
        ];
    }

    // Knowledgebase Categories
    $categories = Capsule::table('tblknowledgebasecats')->get();
    foreach ($categories as $cat) {
        $slug = sitemap_slugify($cat->name);
        $urls[] = [
            'loc' => $baseUrl . 'knowledgebase/' . $cat->id . '/' . $slug . '',
            'priority' => '0.6'
        ];
    }

    // Knowledgebase Articles
    $articles = Capsule::table('tblknowledgebase')->get();
    foreach ($articles as $article) {
        $slug = sitemap_slugify($article->title);
        $urls[] = [
            'loc' => $baseUrl . 'knowledgebase/' . $article->id . '/' . $slug . '.html',
            'priority' => '0.7'
        ];
    }

    // Announcements
    $announcements = Capsule::table('tblannouncements')->get();
    foreach ($announcements as $announce) {
        $slug = sitemap_slugify($announce->title);
        $urls[] = [
            'loc' => $baseUrl . 'announcements/' . $announce->id . '/' . $slug . '.html',
            'priority' => '0.7'
        ];
    }

    // Build XML
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
    foreach ($urls as $url) {
        $u = $xml->addChild('url');
        $u->addChild('loc', $url['loc']);
        $u->addChild('priority', $url['priority']);
        $u->addChild('lastmod', date('Y-m-d'));
    }

    $filename = 'sitemap_' . date('Y_m_d_H_i_s') . '.xml';
    $filePath = $moduleDir . DIRECTORY_SEPARATOR . $filename;

    if ($xml->asXML($filePath)) {
        cleanup_old_sitemaps($moduleDir);
        return "Sitemap generated successfully as <code>$filename</code>";
    } else {
        return "Error generating sitemap. Please check file permissions.";
    }
}

/**
 * Helper to make SEO-friendly slugs
 */
function sitemap_slugify($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string); // replace non-alphanumeric with dashes
    $slug = trim($slug, '-'); // remove leading/trailing dashes
    return strtolower($slug);
}

function cleanup_old_sitemaps($moduleDir) {
    $files = glob($moduleDir . "/sitemap_*.xml");
    if (!$files) {
        return;
    }

    // Remove files older than 3 days
    $now = time();
    foreach ($files as $file) {
        if ($now - filemtime($file) > 3 * 86400) {
            @unlink($file);
        }
    }

    // Keep max 5 newest files, delete the rest
    $files = glob($moduleDir . "/sitemap_*.xml");
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    $filesToDelete = array_slice($files, 5);
    foreach ($filesToDelete as $file) {
        @unlink($file);
    }
}

/**
 * Cron hook executed twice daily to generate sitemap automatically
 */
add_hook('DailyCronJob', 1, function($vars) {
    static $count = 0;
    $count++;

    if ($count == 1 || $count == 2) {
        $moduleDir = dirname(__FILE__);
        sitemap_generate($moduleDir);
    }

    if ($count >= 2) {
        $count = 0;
    }
});
