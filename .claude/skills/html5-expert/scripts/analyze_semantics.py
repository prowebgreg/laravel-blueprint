#!/usr/bin/env python3
"""
HTML Semantic Analysis Script

Analyzes HTML files for:
- Semantic element usage vs generic divs/spans
- Document structure and hierarchy
- Accessibility patterns and best practices
- SEO optimization opportunities
- Content organization recommendations

Usage:
    python analyze_semantics.py <html_file>
    python analyze_semantics.py <html_file> --format json
    python analyze_semantics.py <html_file> --verbose
"""

import re
import sys
import json
import argparse
from html.parser import HTMLParser
from typing import List, Dict, Set, Tuple, Optional
from dataclasses import dataclass, asdict
from collections import Counter, defaultdict


@dataclass
class SemanticScore:
    """Overall semantic quality score"""
    total_score: int  # 0-100
    semantic_usage: int  # 0-100
    accessibility: int  # 0-100
    structure: int  # 0-100
    seo: int  # 0-100
    grade: str  # A+, A, B, C, D, F


@dataclass
class Recommendation:
    """Improvement recommendation"""
    category: str
    priority: str  # high, medium, low
    title: str
    description: str
    example: Optional[str] = None
    line: Optional[int] = None


class SemanticAnalyzer(HTMLParser):
    """HTML semantic analysis parser"""
    
    # Semantic elements that indicate good structure
    SEMANTIC_ELEMENTS = {
        'article', 'section', 'nav', 'aside', 'header', 'footer', 'main',
        'figure', 'figcaption', 'details', 'summary', 'mark', 'time'
    }
    
    # Structural landmarks
    LANDMARKS = {
        'header', 'nav', 'main', 'aside', 'footer', 'section', 'article'
    }
    
    # Heading elements
    HEADINGS = {'h1', 'h2', 'h3', 'h4', 'h5', 'h6'}
    
    # Form elements
    FORM_ELEMENTS = {
        'form', 'input', 'textarea', 'select', 'button', 'label',
        'fieldset', 'legend', 'datalist', 'output'
    }
    
    # SEO-important elements
    SEO_ELEMENTS = {
        'title', 'meta', 'h1', 'h2', 'h3', 'img', 'a', 'canonical'
    }
    
    def __init__(self, verbose=False):
        super().__init__()
        self.verbose = verbose
        self.recommendations: List[Recommendation] = []
        
        # Element counters
        self.element_counts = Counter()
        self.semantic_count = 0
        self.generic_count = 0  # div, span
        
        # Document structure
        self.has_doctype = False
        self.has_header = False
        self.has_nav = False
        self.has_main = False
        self.has_footer = False
        self.has_aside = False
        
        # Headings
        self.headings: List[Tuple[str, int, int]] = []  # (tag, line, col)
        self.h1_count = 0
        self.heading_text: Dict[str, List[str]] = defaultdict(list)
        
        # Content organization
        self.has_article = False
        self.has_section = False
        self.article_count = 0
        self.section_count = 0
        
        # Forms
        self.form_count = 0
        self.input_count = 0
        self.label_count = 0
        self.fieldset_count = 0
        
        # Images
        self.img_count = 0
        self.img_with_alt = 0
        self.img_decorative = 0  # empty alt
        
        # Links
        self.link_count = 0
        self.external_links = 0
        self.skip_link = False
        
        # ARIA
        self.aria_usage = Counter()
        self.role_usage = Counter()
        
        # SEO
        self.has_title = False
        self.title_length = 0
        self.has_meta_description = False
        self.meta_description_length = 0
        self.has_meta_viewport = False
        self.has_og_tags = False
        
        # Lists
        self.ul_count = 0
        self.ol_count = 0
        self.dl_count = 0
        
        # Tables
        self.table_count = 0
        self.tables_with_caption = 0
        self.tables_with_thead = 0
        
        # Multimedia
        self.video_count = 0
        self.audio_count = 0
        
        # Current state
        self.current_line = 1
        self.in_head = False
    
    def feed(self, data):
        """Override feed to detect DOCTYPE"""
        if re.search(r'<!DOCTYPE\s+html>', data, re.IGNORECASE):
            self.has_doctype = True
        super().feed(data)
    
    def handle_starttag(self, tag, attrs):
        """Process opening tag"""
        attrs_dict = dict(attrs)
        line, col = self.getpos()
        
        # Count elements
        self.element_counts[tag] += 1
        
        # Track semantic vs generic
        if tag in self.SEMANTIC_ELEMENTS:
            self.semantic_count += 1
        elif tag in ['div', 'span']:
            self.generic_count += 1
        
        # Document structure
        if tag == 'head':
            self.in_head = True
        elif tag == 'body':
            self.in_head = False
        elif tag == 'header':
            self.has_header = True
        elif tag == 'nav':
            self.has_nav = True
            # Check for skip link
            if not self.skip_link and line < 20:  # Early in document
                self.skip_link = True  # Assume exists if nav is early
        elif tag == 'main':
            self.has_main = True
        elif tag == 'footer':
            self.has_footer = True
        elif tag == 'aside':
            self.has_aside = True
        elif tag == 'article':
            self.has_article = True
            self.article_count += 1
        elif tag == 'section':
            self.has_section = True
            self.section_count += 1
        
        # Headings
        if tag in self.HEADINGS:
            self.headings.append((tag, line, col))
            if tag == 'h1':
                self.h1_count += 1
        
        # Forms
        if tag == 'form':
            self.form_count += 1
        elif tag == 'input':
            self.input_count += 1
        elif tag == 'label':
            self.label_count += 1
        elif tag == 'fieldset':
            self.fieldset_count += 1
        
        # Images
        if tag == 'img':
            self.img_count += 1
            if 'alt' in attrs_dict:
                if attrs_dict['alt'] == '':
                    self.img_decorative += 1
                else:
                    self.img_with_alt += 1
        
        # Links
        if tag == 'a':
            self.link_count += 1
            href = attrs_dict.get('href', '')
            if href.startswith('http') or href.startswith('//'):
                self.external_links += 1
        
        # ARIA
        for attr, value in attrs_dict.items():
            if attr.startswith('aria-'):
                self.aria_usage[attr] += 1
            if attr == 'role':
                self.role_usage[value] += 1
        
        # SEO
        if tag == 'title' and self.in_head:
            self.has_title = True
        elif tag == 'meta':
            name = attrs_dict.get('name', '').lower()
            property_attr = attrs_dict.get('property', '').lower()
            
            if name == 'description':
                self.has_meta_description = True
                content = attrs_dict.get('content', '')
                self.meta_description_length = len(content)
            elif name == 'viewport':
                self.has_meta_viewport = True
            elif property_attr.startswith('og:'):
                self.has_og_tags = True
        
        # Lists
        if tag == 'ul':
            self.ul_count += 1
        elif tag == 'ol':
            self.ol_count += 1
        elif tag == 'dl':
            self.dl_count += 1
        
        # Tables
        if tag == 'table':
            self.table_count += 1
        elif tag == 'caption':
            self.tables_with_caption += 1
        elif tag == 'thead':
            self.tables_with_thead += 1
        
        # Multimedia
        if tag == 'video':
            self.video_count += 1
        elif tag == 'audio':
            self.audio_count += 1
    
    def handle_data(self, data):
        """Capture text content"""
        # Store heading text for analysis
        if self.headings:
            last_heading = self.headings[-1][0]
            text = data.strip()
            if text and len(self.heading_text[last_heading]) < 10:  # Limit storage
                self.heading_text[last_heading].append(text)
        
        # Capture title length
        if self.in_head and self.has_title:
            self.title_length = len(data.strip())
    
    def analyze(self) -> Dict:
        """Perform semantic analysis"""
        self._analyze_semantic_usage()
        self._analyze_document_structure()
        self._analyze_accessibility()
        self._analyze_seo()
        self._analyze_content_organization()
        
        # Calculate scores
        scores = self._calculate_scores()
        
        # Sort recommendations by priority
        self.recommendations.sort(key=lambda r: {
            'high': 0, 'medium': 1, 'low': 2
        }.get(r.priority, 3))
        
        return {
            'scores': asdict(scores),
            'statistics': self._get_statistics(),
            'recommendations': [asdict(r) for r in self.recommendations],
            'summary': self._generate_summary(scores)
        }
    
    def _analyze_semantic_usage(self):
        """Analyze use of semantic HTML elements"""
        total_elements = self.semantic_count + self.generic_count
        
        if total_elements > 0:
            semantic_ratio = self.semantic_count / total_elements
            
            # Check for div/span overuse
            if self.generic_count > self.semantic_count * 3:
                self.add_recommendation(
                    'semantic',
                    'high',
                    'Replace generic divs with semantic elements',
                    f'Found {self.generic_count} generic div/span elements vs '
                    f'{self.semantic_count} semantic elements. Replace divs with '
                    f'semantic HTML (article, section, nav, etc.) where appropriate.',
                    '<div class="article"> → <article>'
                )
            elif semantic_ratio < 0.2 and self.generic_count > 20:
                self.add_recommendation(
                    'semantic',
                    'medium',
                    'Increase semantic element usage',
                    f'Only {semantic_ratio:.0%} of container elements are semantic. '
                    f'Consider using more semantic elements for better structure.',
                    '<div class="header"> → <header>'
                )
        
        # Check for specific semantic elements
        if not self.has_article and self.element_counts['div'] > 10:
            self.add_recommendation(
                'semantic',
                'medium',
                'Use <article> for standalone content',
                'Consider wrapping blog posts, news articles, or forum posts in <article> elements.',
                '<article>\n  <h2>Article Title</h2>\n  <p>Content...</p>\n</article>'
            )
        
        if not self.has_section and self.element_counts['div'] > 15:
            self.add_recommendation(
                'semantic',
                'low',
                'Use <section> for thematic content groups',
                'Group related content in <section> elements with headings.',
                '<section>\n  <h2>Section Title</h2>\n  <p>Content...</p>\n</section>'
            )
    
    def _analyze_document_structure(self):
        """Analyze document structure and landmarks"""
        # Check for main landmarks
        if not self.has_main:
            self.add_recommendation(
                'structure',
                'high',
                'Add <main> element for primary content',
                'Wrap the main page content in a <main> element to help users '
                'and assistive technologies identify the primary content.',
                '<main>\n  <!-- Primary page content -->\n</main>'
            )
        
        if not self.has_header and self.element_counts['div'] > 5:
            self.add_recommendation(
                'structure',
                'medium',
                'Add <header> for page header',
                'Use <header> element for the page header/banner area.',
                '<header>\n  <h1>Site Title</h1>\n  <nav>...</nav>\n</header>'
            )
        
        if not self.has_nav and self.link_count > 5:
            self.add_recommendation(
                'structure',
                'high',
                'Wrap navigation links in <nav>',
                'Group navigation links in a <nav> element for better structure.',
                '<nav aria-label="Primary navigation">\n  <ul>...</ul>\n</nav>'
            )
        
        if not self.has_footer and self.element_counts['div'] > 10:
            self.add_recommendation(
                'structure',
                'medium',
                'Add <footer> for page footer',
                'Use <footer> element for footer content.',
                '<footer>\n  <p>© 2025 Company</p>\n</footer>'
            )
        
        # Check heading hierarchy
        if self.headings:
            self._analyze_heading_hierarchy()
    
    def _analyze_heading_hierarchy(self):
        """Analyze heading structure"""
        if self.h1_count == 0:
            self.add_recommendation(
                'structure',
                'high',
                'Add <h1> for main page heading',
                'Every page should have exactly one <h1> element describing the main topic.',
                '<h1>Main Page Title</h1>'
            )
        elif self.h1_count > 1:
            self.add_recommendation(
                'structure',
                'medium',
                'Use only one <h1> per page',
                f'Found {self.h1_count} <h1> elements. Best practice is one <h1> per page.',
                'Use <h1> for main heading, <h2> for sections'
            )
        
        # Check for heading gaps
        heading_levels = [int(tag[1]) for tag, _, _ in self.headings]
        for i in range(len(heading_levels) - 1):
            current = heading_levels[i]
            next_level = heading_levels[i + 1]
            
            if next_level > current + 1:
                _, line, _ = self.headings[i + 1]
                self.add_recommendation(
                    'structure',
                    'medium',
                    f'Heading level skipped (h{current} → h{next_level})',
                    f'Line {line}: Skipped heading level from <h{current}> to <h{next_level}>. '
                    f'Use consecutive heading levels for proper document outline.',
                    f'Use <h{current + 1}> instead of <h{next_level}>',
                    line
                )
    
    def _analyze_accessibility(self):
        """Analyze accessibility features"""
        # Images
        if self.img_count > 0:
            missing_alt = self.img_count - self.img_with_alt - self.img_decorative
            if missing_alt > 0:
                self.add_recommendation(
                    'accessibility',
                    'high',
                    f'Add alt text to {missing_alt} image(s)',
                    'All images must have alt attributes for screen readers.',
                    '<img src="photo.jpg" alt="Description of image">'
                )
            
            alt_ratio = (self.img_with_alt + self.img_decorative) / self.img_count
            if alt_ratio == 1.0 and self.img_decorative == 0 and self.img_count > 5:
                self.add_recommendation(
                    'accessibility',
                    'low',
                    'Consider marking decorative images',
                    'Decorative images should have empty alt text: alt=""',
                    '<img src="decorative.png" alt="">'
                )
        
        # Forms
        if self.input_count > 0:
            if self.label_count < self.input_count * 0.8:
                self.add_recommendation(
                    'accessibility',
                    'high',
                    'Add labels to form inputs',
                    f'Found {self.input_count} inputs but only {self.label_count} labels. '
                    f'All form inputs should have associated labels.',
                    '<label for="email">Email:</label>\n<input type="email" id="email">'
                )
            
            if self.form_count > 0 and self.fieldset_count == 0:
                self.add_recommendation(
                    'accessibility',
                    'medium',
                    'Group related form fields with <fieldset>',
                    'Use <fieldset> and <legend> to group related form controls.',
                    '<fieldset>\n  <legend>Contact Info</legend>\n  ...\n</fieldset>'
                )
        
        # Skip links
        if self.has_nav and not self.skip_link:
            self.add_recommendation(
                'accessibility',
                'medium',
                'Add skip link for keyboard users',
                'Provide a "Skip to main content" link at the start of the page.',
                '<a href="#main-content" class="skip-link">Skip to main content</a>'
            )
        
        # ARIA usage
        if len(self.aria_usage) == 0 and self.element_counts['div'] > 20:
            self.add_recommendation(
                'accessibility',
                'low',
                'Consider ARIA labels for landmark regions',
                'Add aria-label to distinguish multiple navigation or aside elements.',
                '<nav aria-label="Primary navigation">'
            )
        
        # Tables
        if self.table_count > 0:
            if self.tables_with_caption < self.table_count:
                self.add_recommendation(
                    'accessibility',
                    'medium',
                    'Add <caption> to data tables',
                    f'{self.table_count - self.tables_with_caption} table(s) missing captions. '
                    f'Captions help users understand table purpose.',
                    '<table>\n  <caption>Table Description</caption>\n  ...\n</table>'
                )
            
            if self.tables_with_thead < self.table_count:
                self.add_recommendation(
                    'accessibility',
                    'high',
                    'Add <thead> to data tables',
                    'Tables should have <thead> with <th> elements for proper structure.',
                    '<thead>\n  <tr><th>Header</th></tr>\n</thead>'
                )
    
    def _analyze_seo(self):
        """Analyze SEO optimization"""
        # Title
        if not self.has_title:
            self.add_recommendation(
                'seo',
                'high',
                'Add <title> element',
                'Every page must have a unique, descriptive title (50-60 characters).',
                '<title>Page Title - Site Name</title>'
            )
        elif self.title_length < 30:
            self.add_recommendation(
                'seo',
                'medium',
                'Lengthen page title',
                f'Title is only {self.title_length} characters. Optimal length is 50-60 characters.',
                '<title>Descriptive Page Title - Brand Name</title>'
            )
        elif self.title_length > 60:
            self.add_recommendation(
                'seo',
                'medium',
                'Shorten page title',
                f'Title is {self.title_length} characters. May be truncated in search results.',
                'Keep titles under 60 characters'
            )
        
        # Meta description
        if not self.has_meta_description:
            self.add_recommendation(
                'seo',
                'high',
                'Add meta description',
                'Add a unique meta description (150-160 characters) for search results.',
                '<meta name="description" content="Page description...">'
            )
        elif self.meta_description_length < 120:
            self.add_recommendation(
                'seo',
                'medium',
                'Lengthen meta description',
                f'Meta description is {self.meta_description_length} characters. '
                f'Optimal length is 150-160 characters.',
                'Expand description to 150-160 characters'
            )
        
        # Viewport
        if not self.has_meta_viewport:
            self.add_recommendation(
                'seo',
                'high',
                'Add viewport meta tag',
                'Required for mobile-friendly pages and mobile SEO.',
                '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            )
        
        # Open Graph
        if not self.has_og_tags and (self.has_article or self.element_counts['div'] > 20):
            self.add_recommendation(
                'seo',
                'low',
                'Add Open Graph meta tags',
                'Improve social media sharing with Open Graph tags.',
                '<meta property="og:title" content="Page Title">\n'
                '<meta property="og:description" content="Description">\n'
                '<meta property="og:image" content="image.jpg">'
            )
        
        # Heading structure for SEO
        if self.h1_count == 1:
            h2_count = self.element_counts['h2']
            if h2_count == 0 and len(self.headings) < 3:
                self.add_recommendation(
                    'seo',
                    'medium',
                    'Add subheadings (h2, h3) for content structure',
                    'Search engines use heading hierarchy to understand content structure.',
                    'Use h2 for major sections, h3 for subsections'
                )
    
    def _analyze_content_organization(self):
        """Analyze content organization"""
        # Lists
        if self.ul_count == 0 and self.ol_count == 0:
            if self.link_count > 5:
                self.add_recommendation(
                    'organization',
                    'low',
                    'Use lists for navigation links',
                    'Group navigation or related links in <ul> or <ol> lists.',
                    '<ul>\n  <li><a href="/">Home</a></li>\n  <li><a href="/about">About</a></li>\n</ul>'
                )
        
        # Article/Section balance
        if self.article_count > 10:
            self.add_recommendation(
                'organization',
                'low',
                'Consider grouping articles in sections',
                f'Found {self.article_count} articles. Group related articles in <section> elements.',
                '<section>\n  <h2>Category</h2>\n  <article>...</article>\n  <article>...</article>\n</section>'
            )
        
        # External links
        if self.external_links > 5:
            self.add_recommendation(
                'organization',
                'low',
                'Add rel="noopener" to external links',
                f'Found {self.external_links} external links. Add rel="noopener" for security.',
                '<a href="https://external.com" rel="noopener" target="_blank">Link</a>'
            )
    
    def _calculate_scores(self) -> SemanticScore:
        """Calculate overall semantic quality scores"""
        # Semantic usage score (0-100)
        total_containers = self.semantic_count + self.generic_count
        if total_containers > 0:
            semantic_ratio = self.semantic_count / total_containers
            semantic_score = min(100, int(semantic_ratio * 150))  # Boost for good usage
        else:
            semantic_score = 50
        
        # Accessibility score (0-100)
        accessibility_score = 100
        
        # Deduct for missing alt text
        if self.img_count > 0:
            alt_ratio = (self.img_with_alt + self.img_decorative) / self.img_count
            accessibility_score -= int((1 - alt_ratio) * 30)
        
        # Deduct for missing labels
        if self.input_count > 0:
            label_ratio = min(1.0, self.label_count / self.input_count)
            accessibility_score -= int((1 - label_ratio) * 30)
        
        # Deduct for missing skip link
        if self.has_nav and not self.skip_link:
            accessibility_score -= 10
        
        # Deduct for tables without proper structure
        if self.table_count > 0:
            table_quality = (self.tables_with_caption + self.tables_with_thead) / (self.table_count * 2)
            accessibility_score -= int((1 - table_quality) * 20)
        
        accessibility_score = max(0, accessibility_score)
        
        # Structure score (0-100)
        structure_score = 0
        structure_score += 20 if self.has_main else 0
        structure_score += 15 if self.has_header else 0
        structure_score += 15 if self.has_nav else 0
        structure_score += 10 if self.has_footer else 0
        structure_score += 10 if self.h1_count == 1 else (5 if self.h1_count > 0 else 0)
        structure_score += 10 if len(self.headings) >= 3 else 5
        structure_score += 10 if self.has_article or self.has_section else 0
        structure_score += 10 if self.has_doctype else 0
        
        # SEO score (0-100)
        seo_score = 0
        seo_score += 25 if self.has_title else 0
        seo_score += 10 if 30 <= self.title_length <= 60 else 5
        seo_score += 25 if self.has_meta_description else 0
        seo_score += 10 if 120 <= self.meta_description_length <= 160 else 5
        seo_score += 15 if self.has_meta_viewport else 0
        seo_score += 5 if self.has_og_tags else 0
        seo_score += 10 if self.h1_count == 1 else 0
        
        # Overall score (weighted average)
        total_score = int(
            semantic_score * 0.25 +
            accessibility_score * 0.30 +
            structure_score * 0.25 +
            seo_score * 0.20
        )
        
        # Assign grade
        if total_score >= 90:
            grade = 'A+'
        elif total_score >= 85:
            grade = 'A'
        elif total_score >= 80:
            grade = 'A-'
        elif total_score >= 75:
            grade = 'B+'
        elif total_score >= 70:
            grade = 'B'
        elif total_score >= 65:
            grade = 'B-'
        elif total_score >= 60:
            grade = 'C+'
        elif total_score >= 55:
            grade = 'C'
        elif total_score >= 50:
            grade = 'C-'
        elif total_score >= 45:
            grade = 'D+'
        elif total_score >= 40:
            grade = 'D'
        else:
            grade = 'F'
        
        return SemanticScore(
            total_score=total_score,
            semantic_usage=semantic_score,
            accessibility=accessibility_score,
            structure=structure_score,
            seo=seo_score,
            grade=grade
        )
    
    def _get_statistics(self) -> Dict:
        """Get element usage statistics"""
        return {
            'elements': {
                'total_semantic': self.semantic_count,
                'total_generic': self.generic_count,
                'semantic_ratio': round(
                    self.semantic_count / max(1, self.semantic_count + self.generic_count), 2
                )
            },
            'structure': {
                'has_header': self.has_header,
                'has_nav': self.has_nav,
                'has_main': self.has_main,
                'has_footer': self.has_footer,
                'has_aside': self.has_aside,
                'has_article': self.has_article,
                'has_section': self.has_section
            },
            'headings': {
                'h1_count': self.h1_count,
                'total_headings': len(self.headings),
                'heading_counts': {
                    f'h{i}': self.element_counts[f'h{i}'] 
                    for i in range(1, 7)
                }
            },
            'forms': {
                'form_count': self.form_count,
                'input_count': self.input_count,
                'label_count': self.label_count,
                'fieldset_count': self.fieldset_count
            },
            'images': {
                'total': self.img_count,
                'with_alt': self.img_with_alt,
                'decorative': self.img_decorative,
                'missing_alt': self.img_count - self.img_with_alt - self.img_decorative
            },
            'links': {
                'total': self.link_count,
                'external': self.external_links
            },
            'lists': {
                'ul': self.ul_count,
                'ol': self.ol_count,
                'dl': self.dl_count
            },
            'tables': {
                'total': self.table_count,
                'with_caption': self.tables_with_caption,
                'with_thead': self.tables_with_thead
            },
            'aria': {
                'aria_attributes': dict(self.aria_usage),
                'roles': dict(self.role_usage)
            },
            'seo': {
                'has_title': self.has_title,
                'title_length': self.title_length,
                'has_meta_description': self.has_meta_description,
                'meta_description_length': self.meta_description_length,
                'has_meta_viewport': self.has_meta_viewport,
                'has_og_tags': self.has_og_tags
            }
        }
    
    def _generate_summary(self, scores: SemanticScore) -> str:
        """Generate human-readable summary"""
        lines = []
        
        # Overall assessment
        if scores.total_score >= 85:
            lines.append("Excellent semantic HTML! Your page demonstrates strong use of semantic elements and accessibility features.")
        elif scores.total_score >= 70:
            lines.append("Good semantic HTML foundation with room for improvement in some areas.")
        elif scores.total_score >= 55:
            lines.append("Basic semantic structure present, but significant improvements needed.")
        else:
            lines.append("Limited use of semantic HTML. Consider major restructuring for better accessibility and SEO.")
        
        # Specific strengths
        strengths = []
        if scores.semantic_usage >= 75:
            strengths.append("strong semantic element usage")
        if scores.accessibility >= 85:
            strengths.append("excellent accessibility features")
        if scores.structure >= 80:
            strengths.append("well-organized document structure")
        if scores.seo >= 80:
            strengths.append("good SEO optimization")
        
        if strengths:
            lines.append(f"Strengths: {', '.join(strengths)}.")
        
        # Priority improvements
        high_priority = [r for r in self.recommendations if r.priority == 'high']
        if high_priority:
            lines.append(f"\nFocus on {len(high_priority)} high-priority improvement(s) first.")
        
        return ' '.join(lines)
    
    def add_recommendation(self, category: str, priority: str, title: str,
                          description: str, example: str = None, line: int = None):
        """Add a recommendation"""
        self.recommendations.append(Recommendation(
            category=category,
            priority=priority,
            title=title,
            description=description,
            example=example,
            line=line
        ))


def format_text_output(results: Dict, verbose: bool = False) -> str:
    """Format results as human-readable text"""
    output = []
    scores = results['scores']
    stats = results['statistics']
    
    # Header
    output.append(f"\n{'='*70}")
    output.append(f"HTML Semantic Analysis Report")
    output.append(f"{'='*70}\n")
    
    # Overall Score
    grade_emoji = {
        'A+': '🏆', 'A': '⭐', 'A-': '⭐',
        'B+': '✅', 'B': '✅', 'B-': '✅',
        'C+': '⚠️', 'C': '⚠️', 'C-': '⚠️',
        'D+': '❌', 'D': '❌', 'F': '❌'
    }
    emoji = grade_emoji.get(scores['grade'], '📊')
    
    output.append(f"Overall Score: {scores['total_score']}/100 ({scores['grade']}) {emoji}")
    output.append("")
    output.append(f"  Semantic Usage:  {scores['semantic_usage']}/100")
    output.append(f"  Accessibility:   {scores['accessibility']}/100")
    output.append(f"  Structure:       {scores['structure']}/100")
    output.append(f"  SEO:             {scores['seo']}/100")
    output.append("")
    
    # Summary
    output.append("Summary:")
    output.append(f"  {results['summary']}")
    output.append("")
    
    # Quick Stats
    output.append(f"{'='*70}")
    output.append("Quick Statistics")
    output.append(f"{'='*70}\n")
    
    elem_stats = stats['elements']
    output.append(f"Elements: {elem_stats['total_semantic']} semantic, "
                 f"{elem_stats['total_generic']} generic "
                 f"({elem_stats['semantic_ratio']:.0%} semantic)")
    
    heading_stats = stats['headings']
    output.append(f"Headings: {heading_stats['total_headings']} total "
                 f"(H1: {heading_stats['h1_count']})")
    
    img_stats = stats['images']
    if img_stats['total'] > 0:
        output.append(f"Images: {img_stats['total']} total "
                     f"({img_stats['with_alt'] + img_stats['decorative']}/{img_stats['total']} with alt)")
    
    form_stats = stats['forms']
    if form_stats['input_count'] > 0:
        output.append(f"Forms: {form_stats['input_count']} inputs, "
                     f"{form_stats['label_count']} labels")
    
    output.append("")
    
    # Recommendations
    if results['recommendations']:
        output.append(f"{'='*70}")
        output.append(f"Recommendations ({len(results['recommendations'])} total)")
        output.append(f"{'='*70}\n")
        
        # Group by priority
        for priority in ['high', 'medium', 'low']:
            recs = [r for r in results['recommendations'] if r['priority'] == priority]
            if not recs:
                continue
            
            priority_emoji = {'high': '🔴', 'medium': '🟡', 'low': '🔵'}
            priority_label = {'high': 'HIGH PRIORITY', 'medium': 'MEDIUM PRIORITY', 'low': 'LOW PRIORITY'}
            
            output.append(f"\n{priority_emoji[priority]} {priority_label[priority]} ({len(recs)})")
            output.append("-" * 70)
            
            for i, rec in enumerate(recs, 1):
                output.append(f"\n{i}. {rec['title']}")
                output.append(f"   Category: {rec['category'].title()}")
                if rec.get('line'):
                    output.append(f"   Line: {rec['line']}")
                output.append(f"   {rec['description']}")
                if rec.get('example') and verbose:
                    output.append(f"\n   Example:")
                    for line in rec['example'].split('\n'):
                        output.append(f"   {line}")
                output.append("")
    else:
        output.append("\n✨ No recommendations - excellent semantic HTML!\n")
    
    # Detailed Statistics (verbose mode)
    if verbose:
        output.append(f"\n{'='*70}")
        output.append("Detailed Statistics")
        output.append(f"{'='*70}\n")
        
        # Structure
        structure = stats['structure']
        output.append("Document Structure:")
        output.append(f"  Header:  {'✅' if structure['has_header'] else '❌'}")
        output.append(f"  Nav:     {'✅' if structure['has_nav'] else '❌'}")
        output.append(f"  Main:    {'✅' if structure['has_main'] else '❌'}")
        output.append(f"  Aside:   {'✅' if structure['has_aside'] else '❌'}")
        output.append(f"  Footer:  {'✅' if structure['has_footer'] else '❌'}")
        output.append(f"  Article: {'✅' if structure['has_article'] else '❌'}")
        output.append(f"  Section: {'✅' if structure['has_section'] else '❌'}")
        output.append("")
        
        # Headings breakdown
        output.append("Heading Hierarchy:")
        for i in range(1, 7):
            count = heading_stats['heading_counts'][f'h{i}']
            if count > 0:
                output.append(f"  H{i}: {count}")
        output.append("")
        
        # SEO
        seo = stats['seo']
        output.append("SEO Elements:")
        output.append(f"  Title:       {'✅' if seo['has_title'] else '❌'} "
                     f"({seo['title_length']} chars)")
        output.append(f"  Description: {'✅' if seo['has_meta_description'] else '❌'} "
                     f"({seo['meta_description_length']} chars)")
        output.append(f"  Viewport:    {'✅' if seo['has_meta_viewport'] else '❌'}")
        output.append(f"  Open Graph:  {'✅' if seo['has_og_tags'] else '❌'}")
        output.append("")
    
    return "\n".join(output)


def analyze_html_file(filepath: str, verbose: bool = False, 
                     format_type: str = 'text') -> str:
    """
    Analyze semantic HTML usage in a file
    
    Args:
        filepath: Path to HTML file
        verbose: Include detailed statistics
        format_type: Output format ('text' or 'json')
    
    Returns:
        Formatted analysis results
    """
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            html_content = f.read()
    except FileNotFoundError:
        return json.dumps({'error': f'File not found: {filepath}'})
    except Exception as e:
        return json.dumps({'error': f'Error reading file: {str(e)}'})
    
    # Parse and analyze
    analyzer = SemanticAnalyzer(verbose=verbose)
    
    try:
        analyzer.feed(html_content)
        results = analyzer.analyze()
        
        # Add file info
        results['file'] = filepath
        results['verbose'] = verbose
        
        # Format output
        if format_type == 'json':
            return json.dumps(results, indent=2)
        else:
            return format_text_output(results, verbose)
    
    except Exception as e:
        error_result = {
            'error': f'Analysis error: {str(e)}',
            'file': filepath
        }
        if format_type == 'json':
            return json.dumps(error_result, indent=2)
        else:
            return f"❌ Error analyzing HTML: {str(e)}"


def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(
        description='Analyze HTML semantic quality and provide improvement recommendations',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  # Basic analysis
  python analyze_semantics.py index.html
  
  # Verbose output (detailed statistics)
  python analyze_semantics.py index.html --verbose
  
  # JSON output
  python analyze_semantics.py index.html --format json
  
  # Verbose JSON output
  python analyze_semantics.py index.html --verbose --format json
        """
    )
    
    parser.add_argument(
        'file',
        help='HTML file to analyze'
    )
    
    parser.add_argument(
        '--verbose', '-v',
        action='store_true',
        help='Show detailed statistics'
    )
    
    parser.add_argument(
        '--format',
        choices=['text', 'json'],
        default='text',
        help='Output format (default: text)'
    )
    
    args = parser.parse_args()
    
    # Analyze and print results
    results = analyze_html_file(args.file, args.verbose, args.format)
    print(results)


if __name__ == '__main__':
    main()
