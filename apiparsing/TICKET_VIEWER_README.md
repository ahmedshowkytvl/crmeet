# 🎫 Ticket Viewer Web Application

A beautiful web interface for viewing Zoho Desk tickets with summary and detailed views.

## ✨ Features

### 📋 Summary View
- Displays last 20 tickets in beautiful cards
- Shows essential information:
  - Ticket ID
  - Status (with color coding)
  - Created AT
  - Closed AT
  - Summary/Subject

### 🔍 Detailed View
- Click any ticket card to see full details
- Comprehensive information including:
  - Basic Information (ID, Status, Dates, Email, Subject)
  - Details (CF Fields count, Threads, Channel, Category, Priority, Assignee)
  - CF Fields (Custom Fields with values)
  - Custom Fields (Additional custom data)
  - Full Body of Last Thread or Email

## 🚀 How to Run

### Option 1: Quick Launch
```bash
python launch_ticket_viewer.py
```
This will:
- Start the Flask server
- Automatically open your browser
- Navigate to `http://localhost:5000`

### Option 2: Manual Launch
```bash
python ticket_web_viewer.py
```
Then manually open your browser and go to `http://localhost:5000`

## 🎨 Interface Design

### Summary Cards
- **Blue rounded cards** with hover effects
- **Color-coded borders** based on ticket status:
  - 🟢 Green: Open tickets
  - 🔴 Red: Closed tickets
  - 🟠 Orange: In Progress tickets
- **Responsive grid layout** that adapts to screen size

### Detailed Modal
- **Full-screen modal** with blur background
- **Organized sections** for different types of information
- **Responsive design** for mobile and desktop
- **Easy navigation** with close button or Escape key

## 📱 Responsive Design

- **Desktop**: Multi-column grid layout
- **Tablet**: Adaptive column sizing
- **Mobile**: Single column layout
- **Touch-friendly** buttons and interactions

## 🔧 Technical Features

- **Real-time data** from Zoho Desk API
- **Error handling** with user-friendly messages
- **Loading states** for better UX
- **Refresh functionality** to reload tickets
- **Modal system** for detailed views
- **Keyboard shortcuts** (Escape to close modal)

## 🎯 User Experience

1. **Load Page**: Automatically fetches last 20 tickets
2. **Browse Tickets**: Scroll through summary cards
3. **Click Ticket**: Opens detailed modal with full information
4. **View Details**: See all ticket information organized in sections
5. **Close Modal**: Click X, press Escape, or click outside
6. **Refresh**: Click refresh button to reload tickets

## 🌟 Key Benefits

- **Fast Loading**: Only loads summary data initially
- **On-Demand Details**: Detailed information loaded when needed
- **Beautiful UI**: Modern, professional design
- **Easy Navigation**: Intuitive click-to-view functionality
- **Comprehensive Data**: Shows all available ticket information
- **Mobile Friendly**: Works perfectly on all devices

## 📊 Data Displayed

### Summary View
- Ticket ID
- Status
- Created Date/Time
- Closed Date/Time
- Subject/Summary

### Detailed View
- All summary information
- CF Fields count and values
- Custom Fields count and values
- Thread count
- Channel information
- Category and Priority
- Assignee status
- Full thread content
- All custom field data

## 🎨 Color Scheme

- **Primary**: Blue gradient (#667eea to #764ba2)
- **Success**: Green (#4CAF50)
- **Warning**: Orange (#ff9800)
- **Error**: Red (#f44336)
- **Background**: White with subtle shadows
- **Text**: Dark gray (#333) with good contrast

Perfect for professional ticket management with a modern, clean interface! 🎉
