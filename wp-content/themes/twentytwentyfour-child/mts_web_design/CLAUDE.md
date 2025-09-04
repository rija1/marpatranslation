# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **design prototype/mockup** for the Marpa Translation Society website - a Buddhist text translation organization founded in 2015 by Drupon Khen Rinpoche. The HTML and CSS created here will be used as the foundation for implementing the actual WordPress theme and website.

**Purpose**: Design exploration and frontend prototyping before WordPress development

## Architecture & Structure

### Prototype Structure
- **Static HTML mockups** with inline CSS and JavaScript
- Self-contained design explorations with no build system
- Responsive design using CSS Grid and Flexbox
- Professional styling with custom color schemes and typography
- **Ready for WordPress conversion** - styles and layouts designed for theme implementation

### Mockup Pages
- `translation_society_website.html` / `translation_society_website2.html` - Homepage design variations
- `mts_about_page.html` - About page with organization history and founder profile
- `mts_knowledge_hub.html` - Knowledge Hub design with search and categories
- `mts_catalog_page.html` - Product catalog with filtering and e-commerce elements
- Supporting pages for specific content areas (academic, spiritual, product, term pages)

**Note**: These are static design prototypes. Content is placeholder/sample data for design purposes.

### Design System
The site uses a consistent design language across all pages:

**Color Palette:**
- Primary: `#14375d` (deep blue)
- Burgundy: `#8b1538` / `#800020` (maroon accent)
- Gold: `#d4af37` / `#b8860b` (highlighting)
- Cream: `#faf8f5` / `#faf5f0` (background)
- Parchment: `#f7f4f0` (section backgrounds)

**Typography:**
- Primary font: `'Crimson Text', 'Georgia', serif`
- Fallback: `'Times New Roman', serif`
- Scholarly, traditional appearance appropriate for Buddhist texts

**Layout Patterns:**
- Fixed headers with organization branding
- Hero sections with gradient overlays
- Card-based content organization
- Consistent section headers with decorative elements
- Footer with quote and navigation links

### Key Features
- **Responsive navigation** with mobile considerations
- **Search functionality** (frontend mockups, ready for backend integration)
- **Filtering systems** for catalog browsing
- **Timeline components** for organizational history
- **Interactive cards** with hover effects
- **Modal-ready structure** for knowledge hub content
- **E-commerce ready** product cards for translation catalog

## Development Guidelines

### File Organization
- All styles are **inline CSS** within `<style>` tags
- All JavaScript is **inline** within `<script>` tags  
- Images referenced via external URLs (ibb.co hosting)
- No external dependencies or build tools required

### Code Conventions
- **CSS Custom Properties** extensively used for theming
- **BEM-style naming** for component classes
- **Mobile-first responsive** design approach
- **Semantic HTML5** structure throughout
- **Accessibility considerations** with proper ARIA labels where needed

### Content Management
- **Multilingual content** examples in catalog (English, Chinese, German, French)
- **Consistent terminology** across pages for organization concepts
- **Cost-only pricing** philosophy reflected in product presentations
- **Scholar-practitioner** emphasis in all content areas

### WordPress Conversion Readiness
The mockups are structured for easy WordPress theme development:
- **Component-based layouts** ready for PHP template conversion
- **WooCommerce-compatible** product displays and cart functionality
- **Custom Post Types** mapped out (translations, authors, terms)
- **Search and filtering** interfaces ready for backend integration
- **Responsive design patterns** established for all screen sizes

## Working with This Design System

### Making Design Changes
- Modify inline CSS within existing `<style>` sections to refine visual design
- Add JavaScript functionality within existing `<script>` sections for interactive prototypes
- Maintain consistent color scheme using CSS custom properties
- Follow established responsive patterns when adding new sections

### Creating New Page Mockups
- Copy structure from existing pages (header, navigation, footer)
- Implement the established design system and color palette
- Include proper breadcrumb navigation patterns
- Ensure mobile responsiveness at established breakpoints

### Preparing for WordPress Development
- **Extract CSS** from inline styles for theme stylesheets
- **Identify reusable components** for PHP template parts
- **Document content structure** for Custom Fields/ACF planning
- **Plan dynamic functionality** based on interactive mockup features

### Design Review Process
- Test all pages in modern browsers for design consistency
- Verify responsive behavior at mobile breakpoints (768px, 480px)
- Check interactive element designs (search, filters, buttons)
- Validate cross-page navigation and user flow
- Review content hierarchy and readability

This design system provides a solid foundation for WordPress theme development while maintaining the scholarly, traditional aesthetic appropriate for a Buddhist translation society.