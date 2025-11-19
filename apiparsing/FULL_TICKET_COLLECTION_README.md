# Full Ticket Details Collection - README

## Overview
This collection of scripts allows you to get complete details for tickets from Zoho Desk API, including all threads and comments.

## Scripts Available

### 1. `get_full_ticket_details_with_retry.py` - Test Script (20 tickets)
- **Purpose**: Test the collection process with 20 tickets
- **Features**: Smart retry logic, rate limiting handling
- **Time**: ~2-3 minutes
- **Output**: 2 files (detailed data + summary)

### 2. `get_500_tickets_complete.py` - Production Script (500 tickets)
- **Purpose**: Collect full details for 500 tickets
- **Features**: Batch processing, progress tracking, chunked output
- **Time**: ~15-25 minutes
- **Output**: Multiple chunk files (50 tickets each) + summary

## Usage Instructions

### Step 1: Test with 20 tickets first
```bash
python get_full_ticket_details_with_retry.py
```

This will:
- Test the API connection
- Collect 20 tickets with full details
- Show success rate and timing
- Create test output files

### Step 2: Run full 500 tickets collection
```bash
python get_500_tickets_complete.py
```

This will:
- Collect 500 tickets in batches
- Handle rate limiting automatically
- Save progress every 10 tickets
- Create multiple output files

## Features

### Smart Rate Limiting
- Automatically detects rate limit errors
- Waits progressively longer (2, 3, 4, 5 minutes)
- Resumes automatically after waiting

### Progress Tracking
- Shows real-time progress (every 10 tickets)
- Estimates remaining time
- Saves progress to file (can resume if interrupted)

### Data Organization
- **Chunks**: Large datasets split into 50-ticket files
- **Summary**: Complete statistics and analysis
- **Progress**: Backup file during collection

### Error Handling
- Retries failed requests up to 3 times
- Handles network errors gracefully
- Tracks failed tickets separately

## Output Files

### Test Script Output (20 tickets)
```
tickets_full_details_smart_YYYYMMDD_HHMMSS.json
tickets_summary_smart_YYYYMMDD_HHMMSS.json
```

### Production Script Output (500 tickets)
```
tickets_500_chunk_1_YYYYMMDD_HHMMSS.json    (tickets 1-50)
tickets_500_chunk_2_YYYYMMDD_HHMMSS.json    (tickets 51-100)
...
tickets_500_chunk_10_YYYYMMDD_HHMMSS.json   (tickets 451-500)
tickets_500_summary_YYYYMMDD_HHMMSS.json    (complete summary)
```

## Data Structure

### Each Ticket Contains
```json
{
  "ticket": {
    "id": "766285000468199502",
    "ticketNumber": "2839406",
    "subject": "Changes in the extranet",
    "status": "Open",
    "departmentId": "766285000016070029",
    "createdTime": "2025-10-08T15:30:00.000Z",
    "modifiedTime": "2025-10-08T15:30:00.000Z",
    "cf": {
      "cf_closed_by": "Manual"
    }
  },
  "threads": [
    {
      "id": "thread_id",
      "content": "Thread content...",
      "contentType": "html",
      "createdTime": "2025-10-08T15:30:00.000Z"
    }
  ],
  "threads_count": 1,
  "collected_at": "2025-10-08T18:01:01.123456"
}
```

### Summary Report Contains
```json
{
  "collection_date": "2025-10-08T18:01:01.123456",
  "total_tickets_requested": 500,
  "total_tickets_collected": 487,
  "total_threads_collected": 1234,
  "failed_tickets": ["766285000468123456"],
  "failed_count": 13,
  "success_rate": "97.4%",
  "average_threads_per_ticket": 2.5,
  "analysis": {
    "status_distribution": {
      "Open": 234,
      "Closed": 253
    },
    "department_distribution": {
      "766285000016070029": 156,
      "766285000006092035": 331
    },
    "thread_count_distribution": {
      "0": 12,
      "1-5": 345,
      "6-10": 120,
      "10+": 10
    }
  }
}
```

## Rate Limiting Information

### Zoho API Limits
- **Token Refresh**: 10 requests per minute
- **Tickets API**: 100 requests per minute
- **Threads API**: 100 requests per minute

### Our Strategy
- **Delay between requests**: 1.0-1.5 seconds
- **Batch delays**: 2 seconds between batches
- **Rate limit detection**: Automatic retry with progressive delays
- **Maximum wait**: 5 minutes between retries

## Troubleshooting

### Common Issues

1. **"Too many requests" error**
   - Script will automatically wait and retry
   - Wait times increase with each attempt (2, 3, 4, 5 minutes)

2. **Token refresh failures**
   - Check `config.py` for correct credentials
   - Ensure refresh token is valid and not expired

3. **Network timeouts**
   - Script retries up to 3 times per request
   - Increases delay between attempts

4. **Interrupted collection**
   - Progress is saved every 10 tickets
   - Check `progress_500_collection.json` for last status

### Manual Recovery
If collection is interrupted:
1. Check the progress file
2. Note the last processed ticket
3. Modify script to start from that point
4. Resume collection

## Performance Tips

### For Faster Collection
- Reduce delay between requests (but risk rate limiting)
- Use multiple API keys (if available)
- Run during off-peak hours

### For Reliability
- Keep default delays (1.0-1.5 seconds)
- Monitor progress file
- Check logs for errors

## File Sizes
- **20 tickets**: ~1-2 MB
- **500 tickets**: ~25-50 MB (depending on thread count)
- **Chunk files**: ~2-5 MB each

## Next Steps
After collecting the data:
1. Analyze the summary report
2. Process chunk files as needed
3. Import into database or analysis tool
4. Create reports and dashboards

## Support
If you encounter issues:
1. Check the console output for error messages
2. Verify API credentials in `config.py`
3. Ensure stable internet connection
4. Try running the test script first

---
**Note**: Always test with 20 tickets before running the full 500-ticket collection!

