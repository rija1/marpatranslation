# SCSS Development Setup

## VSCode SCSS Compilation

This theme uses SCSS for styling. The `style.scss` file should be compiled to `style.css`.

### VSCode Setup:

1. **Install "Live Sass Compiler" extension** by Glenn Marks
2. **Configure settings** in VSCode settings.json:
   ```json
   "liveSassCompile.settings.formats": [
     {
       "format": "expanded",
       "extensionName": ".css",
       "savePath": "/"
     }
   ],
   "liveSassCompile.settings.generateMap": false
   ```
3. **Watch the SCSS file**: Click "Watch Sass" in VSCode status bar
4. **Auto-compilation**: Any changes to `style.scss` will automatically compile to `style.css`

### SCSS Structure:

- **Variables**: Colors, typography, spacing defined as SCSS variables
- **Mixins**: Reusable button styles, transitions, card styles
- **Components**: Modular styling for header, footer, navigation, cards
- **Responsive**: Mobile-first breakpoints with mixins
- **WordPress Integration**: Proper CSS custom properties for theme.json compatibility

### Key Features:

✅ SCSS variables for all colors and spacing  
✅ Mixins for consistent button and card styling  
✅ Responsive design with breakpoint variables  
✅ WordPress block-specific styling  
✅ Print styles included  
✅ CSS custom properties for WordPress compatibility  

### Development Workflow:

1. Edit `style.scss` file
2. VSCode automatically compiles to `style.css` 
3. WordPress uses the compiled `style.css`
4. File modification time used for cache busting