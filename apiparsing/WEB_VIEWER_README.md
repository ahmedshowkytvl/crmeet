# 🎫 Beautiful Zoho Desk Ticket Viewer

A stunning, modern web interface for viewing and managing Zoho Desk tickets with interactive details.

## ✨ Features

- 🎨 **Beautiful Modern UI** - Gradient backgrounds, smooth animations, and responsive design
- 📱 **Mobile Responsive** - Works perfectly on desktop, tablet, and mobile devices
- 🔍 **Real-time Search** - Filter tickets by subject, number, email, or status
- 📋 **Interactive Cards** - Click any ticket to view detailed information
- 💬 **Thread Details** - View all email threads with sender/receiver information
- 🔄 **Live Refresh** - Update data without reloading the page
- 🎯 **Smart Parsing** - Automatically extracts names and emails from thread data
- 🌙 **Smooth Animations** - Beautiful hover effects and transitions

## 🚀 Quick Start

### Option 1: Simple Launcher
```bash
python launch_web_viewer.py
```

### Option 2: Direct Launch
```bash
python ticket_web_viewer.py
```

### Option 3: Install Dependencies First
```bash
pip install flask
python ticket_web_viewer.py
```

## 🌐 Usage

1. **Launch the Application** - Run one of the commands above
2. **Browser Opens Automatically** - The web interface will open at `http://127.0.0.1:5000`
3. **View Tickets** - See all tickets in beautiful cards
4. **Search & Filter** - Use the search box to find specific tickets
5. **Click for Details** - Click any ticket to see full details and threads
6. **Refresh Data** - Use the refresh button to get latest tickets

## 🎨 Interface Features

### Main Dashboard
- **Gradient Background** - Beautiful purple-blue gradient
- **Ticket Cards** - Color-coded by status (Open=Green, Closed=Red, In Progress=Orange)
- **Search Bar** - Real-time filtering as you type
- **Control Buttons** - Refresh data and toggle views

### Ticket Details Modal
- **Complete Information** - All ticket fields displayed beautifully
- **Thread History** - All email conversations with proper formatting
- **Sender/Receiver Info** - Parsed names and email addresses
- **Direction Indicators** - Clear incoming/outgoing indicators

### Responsive Design
- **Desktop** - Multi-column grid layout
- **Tablet** - Optimized for touch interaction
- **Mobile** - Single column, touch-friendly interface

## 🔧 Technical Details

### Backend (Python/Flask)
- **RESTful API** - Clean API endpoints for data
- **Real-time Updates** - Refresh data without page reload
- **Error Handling** - Graceful error handling and user feedback
- **Thread Processing** - Smart parsing of email data

### Frontend (HTML/CSS/JavaScript)
- **Modern CSS** - Flexbox, Grid, and CSS animations
- **Vanilla JavaScript** - No external dependencies
- **Responsive Design** - Mobile-first approach
- **Smooth UX** - Loading states and smooth transitions

## 📁 File Structure

```
├── ticket_web_viewer.py      # Main web application
├── launch_web_viewer.py      # Simple launcher script
├── templates/
│   └── index.html            # Beautiful HTML template
├── zoho_api.py               # Zoho API integration
├── config.py                 # Configuration settings
└── requirements.txt          # Python dependencies
```

## 🎯 Key Features Explained

### Smart Email Parsing
- Extracts names from `"Name <email@domain.com>"` format
- Handles quoted names and unquoted names
- Shows both sender and receiver information clearly

### Interactive Threads
- **Incoming Messages** - Blue indicators for received emails
- **Outgoing Messages** - Green indicators for sent emails
- **Content Display** - Clean, readable email content
- **Timestamps** - Formatted creation times

### Status Color Coding
- 🟢 **Open** - Green border and status badge
- 🔴 **Closed** - Red border and status badge
- 🟡 **In Progress** - Orange border and status badge

## 🔄 API Endpoints

- `GET /` - Main web interface
- `GET /api/tickets` - Get all tickets
- `GET /api/ticket/<id>/threads` - Get threads for specific ticket
- `GET /api/refresh` - Refresh ticket data

## 🛠️ Customization

### Colors
Edit the CSS variables in `templates/index.html`:
```css
/* Main gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Status colors */
.status-open { background: #d5f4e6; color: #27ae60; }
.status-closed { background: #fadbd8; color: #e74c3c; }
```

### Layout
Modify the grid layout:
```css
.tickets-grid {
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
}
```

## 🚀 Advanced Usage

### Custom Filters
Add custom filtering in the JavaScript:
```javascript
function customFilter(ticket) {
    return ticket.priority === 'High';
}
```

### Additional Fields
Add more ticket fields in the modal:
```html
<div class="detail-row">
    <div class="detail-label">🏷️ Priority:</div>
    <div class="detail-value">${ticket.priority || 'Not Set'}</div>
</div>
```

## 🎉 Enjoy Your Beautiful Ticket Viewer!

This interface provides a modern, user-friendly way to view and manage your Zoho Desk tickets with style and functionality.

**Features:**
- ✅ Beautiful, responsive design
- ✅ Interactive ticket details
- ✅ Real-time search and filtering
- ✅ Thread history with email parsing
- ✅ Mobile-friendly interface
- ✅ Smooth animations and transitions

**Perfect for:**
- 📊 Ticket management and monitoring
- 💬 Email thread analysis
- 📱 Mobile ticket viewing
- 🎨 Professional presentations
- 👥 Team collaboration

---

*Built with ❤️ for the best Zoho Desk experience!*
