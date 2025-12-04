#!/usr/bin/env python3
"""
HTML Validation Script

Validates HTML files for:
- Valid structure and document requirements
- Element nesting rules (content models)
- Required attributes
- ARIA usage and accessibility
- Semantic HTML best practices
- Common mistakes and anti-patterns

Usage:
    python validate_html.py <html_file>
    python validate_html.py <html_file> --strict
    python validate_html.py <html_file> --format json
"""

import re
import sys
import json
import argparse
from html.parser import HTMLParser
from typing import List, Dict, Tuple, Set, Optional
from dataclasses import dataclass, asdict
from enum import Enum


class Severity(Enum):
    """Validation message severity levels"""
    ERROR = "error"      # Critical issues that break standards
    WARNING = "warning"  # Best practice violations
    INFO = "info"        # Suggestions for improvement


@dataclass
class ValidationMessage:
    """Validation message with context"""
    severity: str
    message: str
    line: int
    column: int
    element: str
    rule: str
    suggestion: Optional[str] = None


class HTMLValidator(HTMLParser):
    """HTML validation parser"""
    
    # Void elements (self-closing, cannot have content)
    VOID_ELEMENTS = {
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr'
    }
    
    # Elements that can only contain phrasing content (no block elements)
    PHRASING_ONLY = {
        'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'address', 'dt', 'dd'
    }
    
    # Block-level elements that cannot be in phrasing content
    BLOCK_ELEMENTS = {
        'div', 'section', 'article', 'aside', 'nav', 'header', 'footer',
        'main', 'figure', 'blockquote', 'pre', 'ul', 'ol', 'dl', 'table',
        'form', 'fieldset', 'hr'
    }
    
    # Interactive elements
    INTERACTIVE_ELEMENTS = {
        'a', 'button', 'details', 'embed', 'iframe', 'label',
        'select', 'textarea'
    }
    
    # Elements that require specific attributes
    REQUIRED_ATTRIBUTES = {
        'img': {'alt'},
        'html': {'lang'},
        'area': {'alt'},
        'input[type=image]': {'alt'},
        'iframe': {'title'},
        'form[method=get]': set(),  # No required attrs for GET forms
        'form[method=post]': set(),
    }
    
    # Labelable form elements
    LABELABLE_ELEMENTS = {
        'button', 'input', 'meter', 'output', 'progress', 'select', 'textarea'
    }
    
    def __init__(self, strict_mode=False):
        super().__init__()
        self.strict_mode = strict_mode
        self.messages: List[ValidationMessage] = []
        self.element_stack: List[Tuple[str, int, int]] = []
        self.has_doctype = False
        self.has_html = False
        self.has_head = False
        self.has_body = False
        self.has_title = False
        self.has_main = False
        self.main_count = 0
        self.h1_count = 0
        self.heading_stack: List[int] = []
        self.current_line = 1
        self.current_col = 0
        self.in_head = False
        self.in_body = False
        self.form_inputs: Set[str] = set()
        self.form_labels: Set[str] = set()
        self.ids_used: Set[str] = set()
        self.interactive_nesting_depth = 0
        
    def feed(self, data):
        """Override feed to detect DOCTYPE"""
        # Check for DOCTYPE
        if re.search(r'<!DOCTYPE\s+html>', data, re.IGNORECASE):
            self.has_doctype = True
        super().feed(data)
    
    def handle_starttag(self, tag, attrs):
        """Process opening tag"""
        attrs_dict = dict(attrs)
        
        # Track position
        line, col = self.getpos()
        self.current_line = line
        self.current_col = col
        
        # Track document structure
        if tag == 'html':
            self.has_html = True
            self._check_html_lang(attrs_dict, line, col)
        elif tag == 'head':
            self.has_head = True
            self.in_head = True
        elif tag == 'body':
            self.has_body = True
            self.in_body = True
            self.in_head = False
        elif tag == 'title':
            self.has_title = True
        elif tag == 'main':
            self.main_count += 1
            self.has_main = True
        
        # Track heading hierarchy
        if tag in ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']:
            level = int(tag[1])
            if tag == 'h1':
                self.h1_count += 1
            self._check_heading_hierarchy(level, line, col)
        
        # Check for void element with content
        if tag in self.VOID_ELEMENTS:
            self._check_void_element(tag, line, col)
        
        # Check required attributes
        self._check_required_attributes(tag, attrs_dict, line, col)
        
        # Check ARIA usage
        self._check_aria_attributes(tag, attrs_dict, line, col)
        
        # Check duplicate IDs
        if 'id' in attrs_dict:
            self._check_duplicate_id(attrs_dict['id'], line, col)
        
        # Check nesting rules
        self._check_nesting_rules(tag, line, col)
        
        # Track interactive nesting
        if tag in self.INTERACTIVE_ELEMENTS or 'href' in attrs_dict:
            self.interactive_nesting_depth += 1
        
        # Track form elements for label checking
        if tag == 'input' and 'id' in attrs_dict:
            input_type = attrs_dict.get('type', 'text')
            if input_type != 'hidden':
                self.form_inputs.add(attrs_dict['id'])
        
        if tag == 'label':
            if 'for' in attrs_dict:
                self.form_labels.add(attrs_dict['for'])
        
        # Add to element stack (except void elements)
        if tag not in self.VOID_ELEMENTS:
            self.element_stack.append((tag, line, col))
    
    def handle_endtag(self, tag):
        """Process closing tag"""
        line, col = self.getpos()
        
        # Check for closing void element
        if tag in self.VOID_ELEMENTS:
            self.add_message(
                Severity.ERROR,
                f"Void element <{tag}> should not have closing tag",
                line, col, tag,
                "void_element_close",
                f"Remove </{tag}> - void elements are self-closing"
            )
        
        # Check for unmatched closing tag
        if self.element_stack:
            expected_tag, _, _ = self.element_stack[-1]
            if expected_tag == tag:
                self.element_stack.pop()
            else:
                self.add_message(
                    Severity.ERROR,
                    f"Mismatched closing tag: expected </{expected_tag}>, got </{tag}>",
                    line, col, tag,
                    "mismatched_tag"
                )
        else:
            self.add_message(
                Severity.ERROR,
                f"Closing tag </{tag}> without matching opening tag",
                line, col, tag,
                "unmatched_closing_tag"
            )
        
        # Track interactive nesting
        if tag in self.INTERACTIVE_ELEMENTS:
            self.interactive_nesting_depth = max(0, self.interactive_nesting_depth - 1)
        
        # Track document structure
        if tag == 'head':
            self.in_head = False
        elif tag == 'body':
            self.in_body = False
    
    def add_message(self, severity: Severity, message: str, line: int, col: int,
                   element: str, rule: str, suggestion: str = None):
        """Add validation message"""
        self.messages.append(ValidationMessage(
            severity=severity.value,
            message=message,
            line=line,
            column=col,
            element=element,
            rule=rule,
            suggestion=suggestion
        ))
    
    def _check_html_lang(self, attrs: Dict, line: int, col: int):
        """Check for lang attribute on <html>"""
        if 'lang' not in attrs:
            self.add_message(
                Severity.ERROR,
                "Missing 'lang' attribute on <html> element",
                line, col, "html",
                "html_lang_required",
                "Add lang attribute: <html lang=\"en\">"
            )
    
    def _check_void_element(self, tag: str, line: int, col: int):
        """Check void element doesn't have content (caught by end tag check)"""
        pass  # Handled in handle_endtag
    
    def _check_required_attributes(self, tag: str, attrs: Dict, line: int, col: int):
        """Check for required attributes"""
        # Images require alt attribute
        if tag == 'img' and 'alt' not in attrs:
            self.add_message(
                Severity.ERROR,
                "Missing 'alt' attribute on <img> element",
                line, col, tag,
                "img_alt_required",
                "Add alt text: <img src=\"...\" alt=\"Description\">"
            )
        
        # Iframes require title attribute
        if tag == 'iframe' and 'title' not in attrs:
            self.add_message(
                Severity.ERROR,
                "Missing 'title' attribute on <iframe> element",
                line, col, tag,
                "iframe_title_required",
                "Add title: <iframe src=\"...\" title=\"Description\">"
            )
        
        # Area elements require alt
        if tag == 'area' and 'alt' not in attrs:
            self.add_message(
                Severity.ERROR,
                "Missing 'alt' attribute on <area> element",
                line, col, tag,
                "area_alt_required",
                "Add alt text: <area href=\"...\" alt=\"Description\">"
            )
    
    def _check_aria_attributes(self, tag: str, attrs: Dict, line: int, col: int):
        """Check ARIA attribute usage"""
        aria_attrs = {k: v for k, v in attrs.items() if k.startswith('aria-')}
        
        # Check for redundant ARIA on semantic elements
        if tag == 'button' and 'role' in attrs and attrs['role'] == 'button':
            self.add_message(
                Severity.WARNING,
                "Redundant role='button' on <button> element",
                line, col, tag,
                "redundant_aria_role",
                "Remove role attribute - <button> has implicit button role"
            )
        
        if tag == 'nav' and 'role' in attrs and attrs['role'] == 'navigation':
            self.add_message(
                Severity.WARNING,
                "Redundant role='navigation' on <nav> element",
                line, col, tag,
                "redundant_aria_role",
                "Remove role attribute - <nav> has implicit navigation role"
            )
        
        # Check for aria-label on non-labelable elements
        if 'aria-label' in attrs and tag in ['div', 'span']:
            if 'role' not in attrs:
                self.add_message(
                    Severity.WARNING,
                    f"aria-label on <{tag}> without role attribute may not be announced",
                    line, col, tag,
                    "aria_label_without_role",
                    "Add appropriate role or use semantic HTML"
                )
        
        # Check for aria-hidden on focusable elements
        if 'aria-hidden' in attrs and attrs['aria-hidden'] == 'true':
            if tag in self.INTERACTIVE_ELEMENTS or 'tabindex' in attrs:
                if 'tabindex' not in attrs or attrs.get('tabindex') != '-1':
                    self.add_message(
                        Severity.ERROR,
                        f"aria-hidden='true' on focusable element <{tag}>",
                        line, col, tag,
                        "aria_hidden_focusable",
                        "Add tabindex='-1' or remove aria-hidden"
                    )
    
    def _check_duplicate_id(self, id_value: str, line: int, col: int):
        """Check for duplicate ID values"""
        if id_value in self.ids_used:
            self.add_message(
                Severity.ERROR,
                f"Duplicate ID '{id_value}' - IDs must be unique",
                line, col, "id",
                "duplicate_id",
                "Use unique IDs for each element"
            )
        else:
            self.ids_used.add(id_value)
    
    def _check_nesting_rules(self, tag: str, line: int, col: int):
        """Check element nesting rules"""
        if not self.element_stack:
            return
        
        parent_tag, parent_line, parent_col = self.element_stack[-1]
        
        # Check block elements in phrasing-only parents
        if parent_tag in self.PHRASING_ONLY and tag in self.BLOCK_ELEMENTS:
            self.add_message(
                Severity.ERROR,
                f"Block element <{tag}> cannot be nested in <{parent_tag}>",
                line, col, tag,
                "invalid_nesting",
                f"<{parent_tag}> can only contain phrasing (inline) content"
            )
        
        # Check interactive element nesting
        if tag in self.INTERACTIVE_ELEMENTS and self.interactive_nesting_depth > 0:
            self.add_message(
                Severity.ERROR,
                f"Interactive element <{tag}> nested in another interactive element",
                line, col, tag,
                "nested_interactive",
                "Interactive elements cannot be nested inside each other"
            )
        
        # Check form nesting
        if tag == 'form' and parent_tag == 'form':
            self.add_message(
                Severity.ERROR,
                "Forms cannot be nested inside other forms",
                line, col, tag,
                "nested_forms",
                "Use separate forms or combine into one form"
            )
        
        # Check button nesting
        if tag == 'button':
            for ancestor_tag, _, _ in self.element_stack:
                if ancestor_tag == 'button':
                    self.add_message(
                        Severity.ERROR,
                        "Buttons cannot be nested inside other buttons",
                        line, col, tag,
                        "nested_buttons"
                    )
        
        # Check a (link) nesting
        if tag == 'a':
            for ancestor_tag, _, _ in self.element_stack:
                if ancestor_tag == 'a':
                    self.add_message(
                        Severity.ERROR,
                        "Links cannot be nested inside other links",
                        line, col, tag,
                        "nested_links"
                    )
    
    def _check_heading_hierarchy(self, level: int, line: int, col: int):
        """Check heading hierarchy for skipped levels"""
        if not self.heading_stack:
            if level != 1 and self.strict_mode:
                self.add_message(
                    Severity.WARNING,
                    f"First heading is <h{level}>, should start with <h1>",
                    line, col, f"h{level}",
                    "heading_start_level",
                    "Start with <h1> for proper document outline"
                )
        else:
            last_level = self.heading_stack[-1]
            if level > last_level + 1:
                self.add_message(
                    Severity.WARNING,
                    f"Heading level skipped from <h{last_level}> to <h{level}>",
                    line, col, f"h{level}",
                    "heading_level_skip",
                    f"Use <h{last_level + 1}> next instead of <h{level}>"
                )
        
        # Maintain stack (keep only relevant levels)
        self.heading_stack = [h for h in self.heading_stack if h < level] + [level]
    
    def validate_document_structure(self):
        """Validate overall document structure"""
        # Check for DOCTYPE
        if not self.has_doctype:
            self.add_message(
                Severity.ERROR,
                "Missing DOCTYPE declaration",
                1, 0, "document",
                "missing_doctype",
                "Add <!DOCTYPE html> at the start of the document"
            )
        
        # Check for required elements
        if not self.has_html:
            self.add_message(
                Severity.ERROR,
                "Missing <html> element",
                1, 0, "document",
                "missing_html"
            )
        
        if not self.has_head:
            self.add_message(
                Severity.ERROR,
                "Missing <head> element",
                1, 0, "document",
                "missing_head"
            )
        
        if not self.has_body:
            self.add_message(
                Severity.ERROR,
                "Missing <body> element",
                1, 0, "document",
                "missing_body"
            )
        
        if not self.has_title:
            self.add_message(
                Severity.ERROR,
                "Missing <title> element in <head>",
                1, 0, "document",
                "missing_title",
                "Add <title>Page Title</title> inside <head>"
            )
        
        # Check for multiple <main> elements
        if self.main_count > 1:
            self.add_message(
                Severity.ERROR,
                f"Document has {self.main_count} <main> elements, should have at most 1",
                1, 0, "main",
                "multiple_main",
                "Use only one <main> element per page"
            )
        
        # Check for multiple H1 elements (warning in strict mode)
        if self.h1_count > 1 and self.strict_mode:
            self.add_message(
                Severity.WARNING,
                f"Document has {self.h1_count} <h1> elements, best practice is 1 per page",
                1, 0, "h1",
                "multiple_h1",
                "Use one <h1> for main page heading"
            )
        elif self.h1_count == 0:
            self.add_message(
                Severity.WARNING,
                "Document has no <h1> element",
                1, 0, "document",
                "missing_h1",
                "Add <h1> for main page heading"
            )
        
        # Check for unclosed elements
        if self.element_stack:
            for tag, line, col in self.element_stack:
                self.add_message(
                    Severity.ERROR,
                    f"Unclosed element <{tag}>",
                    line, col, tag,
                    "unclosed_element",
                    f"Add closing tag </{tag}>"
                )
        
        # Check for form inputs without labels
        unlabeled_inputs = self.form_inputs - self.form_labels
        if unlabeled_inputs and self.strict_mode:
            for input_id in unlabeled_inputs:
                self.add_message(
                    Severity.WARNING,
                    f"Form input with id='{input_id}' has no associated label",
                    1, 0, "input",
                    "input_without_label",
                    f"Add <label for='{input_id}'>Label Text</label>"
                )
    
    def get_results(self) -> Dict:
        """Get validation results"""
        errors = [m for m in self.messages if m.severity == Severity.ERROR.value]
        warnings = [m for m in self.messages if m.severity == Severity.WARNING.value]
        info = [m for m in self.messages if m.severity == Severity.INFO.value]
        
        return {
            'valid': len(errors) == 0,
            'total_issues': len(self.messages),
            'error_count': len(errors),
            'warning_count': len(warnings),
            'info_count': len(info),
            'errors': [asdict(m) for m in errors],
            'warnings': [asdict(m) for m in warnings],
            'info': [asdict(m) for m in info],
        }


def format_text_output(results: Dict) -> str:
    """Format results as human-readable text"""
    output = []
    
    # Header
    status = "✅ VALID" if results['valid'] else "❌ INVALID"
    output.append(f"\n{'='*60}")
    output.append(f"HTML Validation Results: {status}")
    output.append(f"{'='*60}\n")
    
    # Summary
    output.append(f"Total Issues: {results['total_issues']}")
    output.append(f"  Errors:   {results['error_count']}")
    output.append(f"  Warnings: {results['warning_count']}")
    output.append(f"  Info:     {results['info_count']}")
    output.append("")
    
    # Errors
    if results['errors']:
        output.append(f"\n{'='*60}")
        output.append("ERRORS (must fix)")
        output.append(f"{'='*60}\n")
        for error in results['errors']:
            output.append(f"❌ Line {error['line']}:{error['column']} - <{error['element']}>")
            output.append(f"   {error['message']}")
            if error.get('suggestion'):
                output.append(f"   💡 {error['suggestion']}")
            output.append("")
    
    # Warnings
    if results['warnings']:
        output.append(f"\n{'='*60}")
        output.append("WARNINGS (best practices)")
        output.append(f"{'='*60}\n")
        for warning in results['warnings']:
            output.append(f"⚠️  Line {warning['line']}:{warning['column']} - <{warning['element']}>")
            output.append(f"   {warning['message']}")
            if warning.get('suggestion'):
                output.append(f"   💡 {warning['suggestion']}")
            output.append("")
    
    # Info
    if results['info']:
        output.append(f"\n{'='*60}")
        output.append("SUGGESTIONS (optional improvements)")
        output.append(f"{'='*60}\n")
        for info in results['info']:
            output.append(f"ℹ️  Line {info['line']}:{info['column']} - <{info['element']}>")
            output.append(f"   {info['message']}")
            if info.get('suggestion'):
                output.append(f"   💡 {info['suggestion']}")
            output.append("")
    
    # Footer
    if results['valid']:
        output.append("\n✅ HTML is valid! No critical errors found.\n")
    else:
        output.append(f"\n❌ HTML has {results['error_count']} error(s) that must be fixed.\n")
    
    return "\n".join(output)


def validate_html_file(filepath: str, strict_mode: bool = False, 
                      format_type: str = 'text') -> str:
    """
    Validate an HTML file
    
    Args:
        filepath: Path to HTML file
        strict_mode: Enable strict validation (more warnings)
        format_type: Output format ('text' or 'json')
    
    Returns:
        Formatted validation results
    """
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            html_content = f.read()
    except FileNotFoundError:
        return json.dumps({'error': f'File not found: {filepath}'})
    except Exception as e:
        return json.dumps({'error': f'Error reading file: {str(e)}'})
    
    # Parse and validate
    validator = HTMLValidator(strict_mode=strict_mode)
    
    try:
        validator.feed(html_content)
        validator.validate_document_structure()
        results = validator.get_results()
        
        # Add file info
        results['file'] = filepath
        results['strict_mode'] = strict_mode
        
        # Format output
        if format_type == 'json':
            return json.dumps(results, indent=2)
        else:
            return format_text_output(results)
    
    except Exception as e:
        error_result = {
            'error': f'Validation error: {str(e)}',
            'file': filepath
        }
        if format_type == 'json':
            return json.dumps(error_result, indent=2)
        else:
            return f"❌ Error validating HTML: {str(e)}"


def main():
    """Main entry point"""
    parser = argparse.ArgumentParser(
        description='Validate HTML files for structure, semantics, and accessibility',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  # Basic validation
  python validate_html.py index.html
  
  # Strict mode (more warnings)
  python validate_html.py index.html --strict
  
  # JSON output
  python validate_html.py index.html --format json
  
  # Strict JSON output
  python validate_html.py index.html --strict --format json
        """
    )
    
    parser.add_argument(
        'file',
        help='HTML file to validate'
    )
    
    parser.add_argument(
        '--strict',
        action='store_true',
        help='Enable strict validation mode (more warnings)'
    )
    
    parser.add_argument(
        '--format',
        choices=['text', 'json'],
        default='text',
        help='Output format (default: text)'
    )
    
    args = parser.parse_args()
    
    # Validate and print results
    results = validate_html_file(args.file, args.strict, args.format)
    print(results)
    
    # Exit with error code if validation failed
    if args.format == 'json':
        result_data = json.loads(results)
        if not result_data.get('valid', False):
            sys.exit(1)
    else:
        if '❌ INVALID' in results or '❌ Error' in results:
            sys.exit(1)


if __name__ == '__main__':
    main()
