
# Soccer Management Dashboard

## Project Description

**Soccer Management Dashboard** is a modern WordPress plugin that provides a complete solution for managing soccer teams, matches, and referee controls directly from your WordPress admin. It features a beautiful, glassmorphism-inspired UI that is fully isolated from your theme, ensuring a consistent and professional appearance on any WordPress site.

This plugin is ideal for soccer leagues, clubs, or organizations that need to manage teams, schedule matches, assign referees, and record match results—all within WordPress.

## Features

- **Team Management:** Create, edit, and delete teams with player rosters and jersey images.
- **Match Management:** Schedule, edit, and delete matches between teams, assign referees, and record match results.
- **Referee Dashboard:** Specialized interface for referees to view and control their assigned matches.
- **Custom Post Types:** Uses WordPress custom post types for teams and matches.
- **AJAX-powered:** All management actions (create, update, delete, fetch) are handled via secure AJAX endpoints for a seamless experience.
- **Theme Isolation:** Advanced CSS and JS ensure the plugin UI does not conflict with any WordPress theme.
- **Role Management:** Adds a custom "Referee" role with specific capabilities.
- **Responsive Design:** Works perfectly on all devices.
- **Modern UI:** Glassmorphism design with smooth animations and dark theme.

## Installation

1. Upload the plugin folder to `/wp-content/plugins/soccer/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the provided shortcodes to display the dashboards

## Usage

### Shortcodes

#### Admin Dashboard

```
[admin_dashboard_widget]
```
Displays the main admin dashboard for team and match management. Only accessible to administrators.

#### Referee Dashboard

```
[start_match_shortcode]
```
Displays the referee interface for match control. Accessible to referees and administrators.

### Theme Compatibility

This plugin is designed to work with any WordPress theme, including:
- Beaver Builder Theme
- Astra Theme
- Twenty Twenty-Four
- Any custom theme

## Styling System

The plugin uses an advanced styling system that completely isolates your plugin's appearance from the existing theme:

1. **Scoped CSS:** All styles are prefixed with `.soccer-plugin-container`
2. **Theme Style Removal:** Automatically removes conflicting theme styles
3. **CSS Specificity:** Uses `!important` declarations to override theme styles
4. **Container Wrapping:** All plugin content is wrapped in a scoped container

#### Example:
```php
// The plugin automatically wraps content in a scoped container
echo '<div class="soccer-plugin-container">';
// Your plugin content here
echo '</div>';
```

## Custom Post Types

### Teams (`team_management`)
- Stores team information
- Player rosters as JSON
- Team jersey images

### Matches (`match_management`)
- Match scheduling
- Team assignments
- Referee assignments
- Match results

## User Roles

### Administrator
- Full access to all features
- Can create, edit, and delete teams and matches
- Access to admin dashboard

### Referee
- Specialized role for match control
- Can view assigned matches
- Can update match results
- Access to referee dashboard

## AJAX Endpoints

The plugin provides several AJAX endpoints for dynamic functionality:

- `your_plugin_create_team` - Create new teams
- `your_plugin_update_team` - Edit existing teams
- `your_plugin_delete_team` - Delete teams
- `your_plugin_fetch_teams` - Get all teams
- `your_plugin_create_match` - Create new matches
- `your_plugin_update_match` - Edit matches
- `your_plugin_delete_match` - Delete matches
- `your_plugin_fetch_matches` - Get all matches
- `your_plugin_fetch_referee_matches` - Get referee's matches
- `fetch_team_members` - Get team player lists
- `save_soccer_match_summary` - Save match results

## File Structure

```
soccer/
├── css/
│   └── style.css              # Main plugin styles (injected via PHP for isolation)
├── js/
│   └── admin-dashboard.js     # JavaScript functionality
├── inc/
│   └── fetch-referees.php     # Referee management
├── templates/
│   ├── admindashbaord.html    # Admin dashboard template
│   └── referee-match-control.html # Referee dashboard template
├── soccer.php                 # Main plugin file
└── README.md                  # This file
```

## Styling Features

### Glassmorphism Design
- Semi-transparent backgrounds
- Backdrop blur effects
- Subtle borders and shadows

### Animations
- Smooth hover effects
- Card lift animations
- Loading states
- Pulse effects

### Color Scheme
- Dark theme with purple accents
- High contrast for accessibility
- Consistent color palette

### Typography
- Inter font family
- Proper font weights
- Responsive text sizing

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Troubleshooting

### Theme Conflicts
If you experience styling issues:
1. **Check Console:** Look for CSS conflicts in browser developer tools
2. **Clear Cache:** Clear any caching plugins
3. **Check Z-Index:** Ensure plugin container has proper z-index
4. **Theme Specificity:** Some themes may need additional CSS overrides

### Common Issues
**Plugin styles not loading:**
- Ensure shortcode is properly placed
- Check if theme is blocking CSS
- Verify plugin is activated

**Forms not styled correctly:**
- Check for theme form overrides
- Ensure proper CSS specificity
- Verify container class is applied

**Responsive issues:**
- Test on different screen sizes
- Check mobile-specific CSS
- Verify viewport meta tag

## Development

### Adding Custom Styles
To add custom styles that won't conflict with themes:
```css
/* Always scope to the plugin container */
.soccer-plugin-container .your-custom-class {
  /* Your styles here */
  color: #e2e8f0 !important;
  background: rgba(255, 255, 255, 0.1) !important;
}
```

### Modifying Templates
When modifying the HTML templates:
1. Keep the existing structure
2. Add classes within the scoped container
3. Test with different themes
4. Ensure responsive design

## Support
For support and feature requests, please contact the plugin developer.

## License
This plugin is licensed under the GPL v2 or later.

---

**Note:** This plugin is designed to work independently of your theme while maintaining a professional appearance. The styling system ensures that your soccer management interface will look consistent regardless of the active WordPress theme.
