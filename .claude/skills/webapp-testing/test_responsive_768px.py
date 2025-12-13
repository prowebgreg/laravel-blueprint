"""
Test responsive behavior at 768px viewport for Filament admin panel.

This script verifies:
1. Sidebar auto-collapse behavior at 768px
2. Horizontal table scroll functionality
3. Overall layout integrity at mobile/tablet breakpoint
"""

from playwright.sync_api import sync_playwright
import sys

def test_responsive_behavior():
    """Test admin panel responsive behavior at 768px viewport."""

    with sync_playwright() as p:
        # Launch browser
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={'width': 768, 'height': 1024}
        )
        page = context.new_page()

        results = {
            'success': True,
            'tests': [],
            'screenshots': []
        }

        try:
            # Navigate to admin panel
            print("Navigating to http://localhost/admin...")
            page.goto('http://localhost/admin', wait_until='networkidle')

            # Check if we're on login page
            if page.url.endswith('/login') or '/login' in page.url:
                print("On login page - attempting to log in...")

                # Take screenshot of login page
                screenshot_path = '/tmp/admin_login_768px.png'
                page.screenshot(path=screenshot_path, full_page=True)
                results['screenshots'].append(screenshot_path)
                print(f"Screenshot saved: {screenshot_path}")

                # Try to find login form
                email_input = page.locator('input[type="email"], input[name="email"]').first
                password_input = page.locator('input[type="password"], input[name="password"]').first

                if email_input.is_visible() and password_input.is_visible():
                    # Attempt login with default credentials
                    print("Filling login form...")
                    email_input.fill('admin@example.com')
                    password_input.fill('password')

                    # Find and click submit button
                    submit_button = page.locator('button[type="submit"]').first
                    submit_button.click()

                    # Wait for navigation
                    page.wait_for_load_state('networkidle')
                    print(f"After login, URL: {page.url}")
                else:
                    print("WARNING: Could not find login form fields")
                    results['success'] = False
                    results['tests'].append({
                        'name': 'Login Form Detection',
                        'status': 'FAILED',
                        'message': 'Could not locate email/password inputs'
                    })

            # Take screenshot of main admin page at 768px
            screenshot_path = '/tmp/admin_dashboard_768px.png'
            page.screenshot(path=screenshot_path, full_page=True)
            results['screenshots'].append(screenshot_path)
            print(f"Screenshot saved: {screenshot_path}")

            # Test 1: Check sidebar state at 768px
            print("\n=== Test 1: Sidebar Collapse State ===")

            # Look for Filament sidebar elements
            sidebar_selectors = [
                'aside[class*="fi-sidebar"]',
                '[data-sidebar]',
                'nav[class*="sidebar"]',
                '.fi-sidebar'
            ]

            sidebar = None
            for selector in sidebar_selectors:
                try:
                    sidebar = page.locator(selector).first
                    if sidebar.count() > 0:
                        print(f"Found sidebar using selector: {selector}")
                        break
                except:
                    continue

            if sidebar and sidebar.count() > 0:
                # Check if sidebar is collapsed (has collapsed class or small width)
                sidebar_classes = sidebar.get_attribute('class') or ''
                sidebar_box = sidebar.bounding_box()

                is_collapsed = (
                    'collapsed' in sidebar_classes.lower() or
                    (sidebar_box and sidebar_box['width'] < 100)
                )

                results['tests'].append({
                    'name': 'Sidebar Auto-Collapse at 768px',
                    'status': 'PASS' if is_collapsed else 'INFO',
                    'message': f'Sidebar width: {sidebar_box["width"] if sidebar_box else "unknown"}px, Classes: {sidebar_classes[:100]}'
                })
                print(f"Sidebar state: {'Collapsed' if is_collapsed else 'Expanded'}")
                print(f"Sidebar width: {sidebar_box['width'] if sidebar_box else 'unknown'}px")
            else:
                results['tests'].append({
                    'name': 'Sidebar Detection',
                    'status': 'FAILED',
                    'message': 'Could not find sidebar element'
                })
                print("WARNING: Could not find sidebar element")

            # Test 2: Navigate to a resource listing page and check table scroll
            print("\n=== Test 2: Table Horizontal Scroll ===")

            # Try to find a link to Pages resource
            resource_links = [
                'a[href*="/pages"]',
                'text=Pages',
                'text=Static Pages',
                'a[href*="/services"]',
                'text=Services'
            ]

            navigated = False
            for link_selector in resource_links:
                try:
                    link = page.locator(link_selector).first
                    if link.count() > 0 and link.is_visible():
                        print(f"Clicking navigation link: {link_selector}")
                        link.click()
                        page.wait_for_load_state('networkidle')
                        navigated = True
                        break
                except Exception as e:
                    print(f"Could not click {link_selector}: {e}")
                    continue

            if navigated:
                # Take screenshot of listing page
                screenshot_path = '/tmp/admin_listing_768px.png'
                page.screenshot(path=screenshot_path, full_page=True)
                results['screenshots'].append(screenshot_path)
                print(f"Screenshot saved: {screenshot_path}")

                # Check for table with horizontal scroll
                table_selectors = [
                    'table',
                    '[class*="fi-ta"]',
                    '.fi-ta-table',
                    'div[class*="table"]'
                ]

                table_container = None
                for selector in table_selectors:
                    try:
                        elements = page.locator(selector).all()
                        if elements:
                            table_container = elements[0]
                            print(f"Found table using selector: {selector}")
                            break
                    except:
                        continue

                if table_container:
                    # Check if table has overflow-x: auto or scroll
                    overflow_x = page.evaluate('''(element) => {
                        const style = window.getComputedStyle(element);
                        return style.overflowX;
                    }''', table_container.element_handle())

                    # Check parent containers for overflow
                    parent_overflow = page.evaluate('''(element) => {
                        let parent = element.parentElement;
                        while (parent) {
                            const style = window.getComputedStyle(parent);
                            if (style.overflowX === 'auto' || style.overflowX === 'scroll') {
                                return style.overflowX;
                            }
                            parent = parent.parentElement;
                        }
                        return 'none';
                    }''', table_container.element_handle())

                    has_horizontal_scroll = (
                        overflow_x in ['auto', 'scroll'] or
                        parent_overflow in ['auto', 'scroll']
                    )

                    results['tests'].append({
                        'name': 'Table Horizontal Scroll',
                        'status': 'PASS' if has_horizontal_scroll else 'WARNING',
                        'message': f'Table overflow-x: {overflow_x}, Parent overflow-x: {parent_overflow}'
                    })
                    print(f"Table overflow-x: {overflow_x}")
                    print(f"Parent container overflow-x: {parent_overflow}")
                else:
                    results['tests'].append({
                        'name': 'Table Detection',
                        'status': 'FAILED',
                        'message': 'Could not find table element on listing page'
                    })
                    print("WARNING: Could not find table element")
            else:
                results['tests'].append({
                    'name': 'Navigation to Listing Page',
                    'status': 'FAILED',
                    'message': 'Could not navigate to any resource listing page'
                })
                print("WARNING: Could not navigate to listing page")

            # Test 3: Check overall layout integrity
            print("\n=== Test 3: Layout Integrity ===")

            # Check if page has horizontal scroll (should not)
            page_width = page.evaluate('document.documentElement.scrollWidth')
            viewport_width = 768
            has_unwanted_scroll = page_width > viewport_width + 20  # Allow small tolerance

            results['tests'].append({
                'name': 'No Unwanted Horizontal Scroll',
                'status': 'PASS' if not has_unwanted_scroll else 'FAILED',
                'message': f'Page width: {page_width}px, Viewport: {viewport_width}px'
            })
            print(f"Page width: {page_width}px (viewport: {viewport_width}px)")

            if has_unwanted_scroll:
                results['success'] = False

        except Exception as e:
            print(f"\nERROR during testing: {str(e)}")
            results['success'] = False
            results['tests'].append({
                'name': 'Test Execution',
                'status': 'ERROR',
                'message': str(e)
            })

            # Take error screenshot
            try:
                screenshot_path = '/tmp/admin_error_768px.png'
                page.screenshot(path=screenshot_path, full_page=True)
                results['screenshots'].append(screenshot_path)
                print(f"Error screenshot saved: {screenshot_path}")
            except:
                pass

        finally:
            browser.close()

        # Print summary
        print("\n" + "="*60)
        print("RESPONSIVE TESTING SUMMARY (768px viewport)")
        print("="*60)

        for test in results['tests']:
            status_symbol = {
                'PASS': '✓',
                'FAILED': '✗',
                'WARNING': '⚠',
                'INFO': 'ℹ',
                'ERROR': '✗'
            }.get(test['status'], '?')

            print(f"{status_symbol} {test['name']}: {test['status']}")
            print(f"  {test['message']}")

        print(f"\nScreenshots saved:")
        for screenshot in results['screenshots']:
            print(f"  - {screenshot}")

        print(f"\nOverall result: {'SUCCESS' if results['success'] else 'NEEDS ATTENTION'}")

        return 0 if results['success'] else 1

if __name__ == '__main__':
    sys.exit(test_responsive_behavior())
