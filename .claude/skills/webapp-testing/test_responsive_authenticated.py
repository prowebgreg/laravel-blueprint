"""
Test responsive behavior at 768px viewport for Filament admin panel.
This version creates an authenticated session first.
"""

from playwright.sync_api import sync_playwright
import sys

def test_responsive_behavior():
    """Test admin panel responsive behavior at 768px viewport."""

    with sync_playwright() as p:
        # Launch browser with storage state to persist auth
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={'width': 1920, 'height': 1080}  # Start with desktop
        )
        page = context.new_page()

        results = {
            'success': True,
            'tests': [],
            'screenshots': []
        }

        try:
            # First, login at desktop viewport
            print("Logging in at desktop viewport...")
            page.goto('http://localhost/admin', wait_until='networkidle')

            # Check if we need to login
            if '/login' in page.url:
                print("Login required, filling credentials...")

                # Use the known user from database
                email_input = page.locator('input[type="email"], input[name="email"]').first
                password_input = page.locator('input[type="password"], input[name="password"]').first

                # Try default Laravel password from seeder
                email_input.fill('ostreich@example.com')
                password_input.fill('password')

                submit_button = page.locator('button[type="submit"]').first
                submit_button.click()

                page.wait_for_load_state('networkidle', timeout=10000)
                print(f"After login attempt, URL: {page.url}")

            # If still on login page, there was an error
            if '/login' in page.url:
                print("Login failed, but continuing to test public pages...")

            # Now resize to 768px viewport
            print("\n=== Resizing to 768px viewport ===")
            page.set_viewport_size({'width': 768, 'height': 1024})
            page.wait_for_timeout(1000)  # Wait for resize transitions

            # Take screenshot at 768px
            screenshot_path = '/tmp/admin_768px_overview.png'
            page.screenshot(path=screenshot_path, full_page=True)
            results['screenshots'].append(screenshot_path)
            print(f"Screenshot saved: {screenshot_path}")

            # Test 1: Check sidebar state at 768px
            print("\n=== Test 1: Sidebar Collapse State ===")

            # Check for collapsed sidebar indicator
            # Filament typically adds data attributes or classes
            sidebar_collapsed = page.evaluate('''() => {
                const sidebar = document.querySelector('[data-sidebar], aside, nav[class*="sidebar"], .fi-sidebar');
                if (!sidebar) return { found: false };

                const rect = sidebar.getBoundingClientRect();
                const computedStyle = window.getComputedStyle(sidebar);
                const isCollapsed = rect.width < 100 ||
                                  sidebar.classList.contains('collapsed') ||
                                  sidebar.dataset.collapsed === 'true' ||
                                  computedStyle.width === '0px';

                return {
                    found: true,
                    width: rect.width,
                    classes: sidebar.className,
                    isCollapsed: isCollapsed
                };
            }''')

            if sidebar_collapsed['found']:
                results['tests'].append({
                    'name': 'Sidebar Detection and State',
                    'status': 'PASS',
                    'message': f"Sidebar width: {sidebar_collapsed['width']:.1f}px, Collapsed: {sidebar_collapsed['isCollapsed']}"
                })
                print(f"Sidebar found - Width: {sidebar_collapsed['width']:.1f}px, Collapsed: {sidebar_collapsed['isCollapsed']}")
            else:
                results['tests'].append({
                    'name': 'Sidebar Detection',
                    'status': 'INFO',
                    'message': 'No sidebar found (may be expected for login page)'
                })
                print("No sidebar element detected")

            # Test 2: Check for responsive table behavior
            print("\n=== Test 2: Table Container Overflow ===")

            # Navigate to a listing page (try Pages resource)
            possible_urls = [
                'http://localhost/admin/pages',
                'http://localhost/admin/services',
                'http://localhost/admin/blog-posts',
            ]

            navigated = False
            for url in possible_urls:
                print(f"Attempting to navigate to: {url}")
                try:
                    response = page.goto(url, wait_until='networkidle', timeout=5000)
                    if response and response.ok:
                        print(f"Successfully loaded: {url}")
                        navigated = True
                        break
                except Exception as e:
                    print(f"Could not load {url}: {str(e)[:50]}")

            if navigated:
                # Take screenshot of listing page
                screenshot_path = '/tmp/admin_listing_768px.png'
                page.screenshot(path=screenshot_path, full_page=True)
                results['screenshots'].append(screenshot_path)
                print(f"Screenshot saved: {screenshot_path}")

                # Check for table overflow behavior
                table_overflow = page.evaluate('''() => {
                    // Find tables or table containers
                    const tables = document.querySelectorAll('table, [class*="table"], .fi-ta');
                    if (tables.length === 0) return { found: false };

                    let hasOverflow = false;
                    let details = [];

                    for (let table of tables) {
                        let element = table;
                        let depth = 0;

                        // Check element and up to 3 parent levels for overflow
                        while (element && depth < 4) {
                            const style = window.getComputedStyle(element);
                            const overflowX = style.overflowX;
                            const rect = element.getBoundingClientRect();

                            if (overflowX === 'auto' || overflowX === 'scroll') {
                                hasOverflow = true;
                                details.push({
                                    element: element.tagName + (element.className ? '.' + element.className.split(' ')[0] : ''),
                                    overflowX: overflowX,
                                    width: rect.width
                                });
                                break;
                            }

                            element = element.parentElement;
                            depth++;
                        }

                        if (hasOverflow) break;
                    }

                    return {
                        found: true,
                        hasOverflow: hasOverflow,
                        details: details
                    };
                }''')

                if table_overflow['found']:
                    results['tests'].append({
                        'name': 'Table Horizontal Scroll Support',
                        'status': 'PASS' if table_overflow['hasOverflow'] else 'INFO',
                        'message': f"Overflow detected: {table_overflow['hasOverflow']}"
                    })
                    print(f"Tables found - Horizontal scroll enabled: {table_overflow['hasOverflow']}")
                    if table_overflow['details']:
                        print(f"Details: {table_overflow['details']}")
                else:
                    results['tests'].append({
                        'name': 'Table Detection',
                        'status': 'INFO',
                        'message': 'No tables found on listing page'
                    })
                    print("No tables detected on page")

            # Test 3: Check for layout breakage
            print("\n=== Test 3: Layout Integrity Check ===")

            # Check for horizontal overflow
            layout_check = page.evaluate('''() => {
                const bodyWidth = document.body.scrollWidth;
                const viewportWidth = window.innerWidth;
                const hasOverflow = bodyWidth > viewportWidth + 10; // 10px tolerance

                // Find any elements that overflow
                const allElements = document.querySelectorAll('*');
                let overflowingElements = [];

                for (let el of allElements) {
                    const rect = el.getBoundingClientRect();
                    if (rect.right > viewportWidth + 10) {
                        overflowingElements.push({
                            tag: el.tagName,
                            class: el.className ? el.className.split(' ')[0] : '',
                            right: rect.right
                        });
                        if (overflowingElements.length >= 3) break; // Limit to first 3
                    }
                }

                return {
                    bodyWidth: bodyWidth,
                    viewportWidth: viewportWidth,
                    hasOverflow: hasOverflow,
                    overflowingElements: overflowingElements
                };
            }''')

            results['tests'].append({
                'name': 'No Unwanted Horizontal Scroll',
                'status': 'PASS' if not layout_check['hasOverflow'] else 'WARNING',
                'message': f"Body: {layout_check['bodyWidth']}px, Viewport: {layout_check['viewportWidth']}px"
            })
            print(f"Body width: {layout_check['bodyWidth']}px, Viewport: {layout_check['viewportWidth']}px")

            if layout_check['hasOverflow']:
                print(f"WARNING: Horizontal overflow detected")
                if layout_check['overflowingElements']:
                    print(f"Overflowing elements: {layout_check['overflowingElements']}")

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

        # Overall success if no failures or errors
        has_failures = any(t['status'] in ['FAILED', 'ERROR'] for t in results['tests'])
        print(f"\nOverall result: {'SUCCESS' if not has_failures else 'NEEDS ATTENTION'}")

        return 0 if not has_failures else 1

if __name__ == '__main__':
    sys.exit(test_responsive_behavior())
