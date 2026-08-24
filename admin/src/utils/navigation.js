/**
 * Single source of truth for the sidebar's grouped nav and the topbar's page
 * title. Both read this file, so a route's label is written once.
 *
 * `adminOnly` mirrors the backend's `role.admin` middleware on /admin/users and
 * /admin/settings — the server enforces it regardless, but hiding the links
 * stops editors walking into a 403.
 */
export const navGroups = [
  {
    label: 'Overview',
    items: [{ title: 'Dashboard', icon: 'grid', to: '/' }],
  },
  {
    label: 'Enquiries',
    items: [
      { title: 'Enquiries', icon: 'inbox', to: '/enquiries' },
      { title: 'Newsletter Subscribers', icon: 'mail', to: '/subscribers' },
    ],
  },
  {
    label: 'Content',
    items: [
      { title: 'Posts & Case Studies', icon: 'file-text', to: '/posts' },
      { title: 'Resources', icon: 'download', to: '/resources' },
      { title: 'Gallery', icon: 'image', to: '/gallery' },
      { title: 'Page Content', icon: 'layout', to: '/page-content' },
    ],
  },
  {
    label: 'Careers',
    items: [
      { title: 'Job Openings', icon: 'briefcase', to: '/jobs' },
      { title: 'Job Applications', icon: 'user-check', to: '/job-applications' },
    ],
  },
  {
    label: 'Commerce',
    items: [
      { title: 'Orders', icon: 'shopping-cart', to: '/orders' },
      { title: 'Products', icon: 'package', to: '/products' },
      { title: 'Product Categories', icon: 'tag', to: '/product-categories' },
      { title: 'Inventory', icon: 'boxes', to: '/inventory' },
      { title: 'Warehouses', icon: 'warehouse', to: '/warehouses' },
    ],
  },
  {
    label: 'Company',
    items: [
      { title: 'Solutions Catalogue', icon: 'layers', to: '/solutions' },
      { title: 'Clients & Partners', icon: 'handshake', to: '/partners' },
      { title: 'Office Locations', icon: 'map-pin', to: '/office-locations' },
    ],
  },
  {
    label: 'Administration',
    items: [
      { title: 'Settings', icon: 'settings', to: '/settings', adminOnly: true },
      { title: 'Admin Users', icon: 'users', to: '/users', adminOnly: true },
    ],
  },
]

const flatItems = navGroups.flatMap((group) => group.items)

export function pageTitleForPath(path) {
  const exact = flatItems.find((item) => item.to === path)
  if (exact) return exact.title

  const prefixMatch = flatItems
    .filter((item) => item.to !== '/' && path.startsWith(`${item.to}/`))
    .sort((a, b) => b.to.length - a.to.length)[0]
  if (prefixMatch) return prefixMatch.title

  return 'Admin'
}
