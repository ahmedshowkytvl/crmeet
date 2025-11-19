#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Database Models for Zoho Tickets Microservice
نماذج قاعدة البيانات لخدمة تذاكر Zoho الصغيرة
"""

from sqlalchemy import create_engine, Column, Integer, BigInteger, String, Text, DateTime, Boolean, ForeignKey, Index
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, relationship
from datetime import datetime
import json

Base = declarative_base()

class Ticket(Base):
    __tablename__ = 'tickets'
    
    id = Column(BigInteger, primary_key=True)
    ticket_number = Column(String(50), nullable=False, index=True)
    subject = Column(Text)
    status = Column(String(50), index=True)
    created_time = Column(DateTime, index=True)
    closed_time = Column(DateTime, index=True)
    email = Column(String(255))
    phone = Column(String(50))
    department_id = Column(String(50), index=True)
    assignee_id = Column(String(50), index=True)
    priority = Column(String(50))
    category = Column(String(100))
    sub_category = Column(String(100))
    channel = Column(String(50))
    thread_count = Column(Integer, default=0)
    comment_count = Column(Integer, default=0)
    attachment_count = Column(Integer, default=0)
    task_count = Column(Integer, default=0)
    follower_count = Column(Integer, default=0)
    layout_id = Column(String(50))
    contact_id = Column(String(50))
    relationship_type = Column(String(50))
    language = Column(String(10))
    status_type = Column(String(50))
    is_spam = Column(Boolean, default=False)
    is_archived = Column(Boolean, default=False)
    onhold_time = Column(DateTime)
    classification = Column(String(100))
    resolution = Column(Text)
    created_by = Column(String(100))
    modified_by = Column(String(100))
    cf_closed_by = Column(String(100), index=True)
    processing_time = Column(String(50))
    last_updated = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, index=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    custom_fields = relationship("TicketCustomField", back_populates="ticket", cascade="all, delete-orphan")
    threads = relationship("TicketThread", back_populates="ticket", cascade="all, delete-orphan")
    
    def __repr__(self):
        return f"<Ticket(id={self.id}, number={self.ticket_number}, status={self.status})>"
    
    def to_dict(self):
        """Convert ticket to dictionary"""
        return {
            'id': self.id,
            'ticket_number': self.ticket_number,
            'subject': self.subject,
            'status': self.status,
            'created_time': self.created_time.isoformat() if self.created_time else None,
            'closed_time': self.closed_time.isoformat() if self.closed_time else None,
            'email': self.email,
            'phone': self.phone,
            'department_id': self.department_id,
            'assignee_id': self.assignee_id,
            'priority': self.priority,
            'category': self.category,
            'sub_category': self.sub_category,
            'channel': self.channel,
            'thread_count': self.thread_count,
            'comment_count': self.comment_count,
            'attachment_count': self.attachment_count,
            'task_count': self.task_count,
            'follower_count': self.follower_count,
            'layout_id': self.layout_id,
            'contact_id': self.contact_id,
            'relationship_type': self.relationship_type,
            'language': self.language,
            'status_type': self.status_type,
            'is_spam': self.is_spam,
            'is_archived': self.is_archived,
            'onhold_time': self.onhold_time.isoformat() if self.onhold_time else None,
            'classification': self.classification,
            'resolution': self.resolution,
            'created_by': self.created_by,
            'modified_by': self.modified_by,
            'cf_closed_by': self.cf_closed_by,
            'processing_time': self.processing_time,
            'last_updated': self.last_updated.isoformat() if self.last_updated else None,
            'created_at': self.created_at.isoformat() if self.created_at else None
        }

class TicketCustomField(Base):
    __tablename__ = 'ticket_custom_fields'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    ticket_id = Column(BigInteger, ForeignKey('tickets.id', ondelete='CASCADE'), nullable=False, index=True)
    field_name = Column(String(100), nullable=False, index=True)
    field_value = Column(Text)
    field_type = Column(String(50), default='cf', index=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    ticket = relationship("Ticket", back_populates="custom_fields")
    
    def __repr__(self):
        return f"<TicketCustomField(ticket_id={self.ticket_id}, name={self.field_name}, value={self.field_value})>"

class TicketThread(Base):
    __tablename__ = 'ticket_threads'
    
    id = Column(String(100), primary_key=True)
    ticket_id = Column(BigInteger, ForeignKey('tickets.id', ondelete='CASCADE'), nullable=False, index=True)
    thread_type = Column(String(50), index=True)
    direction = Column(String(10), index=True)
    content = Column(Text)
    summary = Column(Text)
    body = Column(Text)
    from_address = Column(String(255))
    to_address = Column(String(255))
    cc_address = Column(String(255))
    bcc_address = Column(String(255))
    created_time = Column(DateTime, index=True)
    modified_time = Column(DateTime)
    is_internal = Column(Boolean, default=False)
    is_public = Column(Boolean, default=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    ticket = relationship("Ticket", back_populates="threads")
    
    def __repr__(self):
        return f"<TicketThread(id={self.id}, ticket_id={self.ticket_id}, type={self.thread_type})>"

class SyncStatus(Base):
    __tablename__ = 'sync_status'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    sync_type = Column(String(50), nullable=False, index=True)
    last_sync_time = Column(DateTime, index=True)
    total_tickets_synced = Column(Integer, default=0)
    new_tickets = Column(Integer, default=0)
    updated_tickets = Column(Integer, default=0)
    failed_tickets = Column(Integer, default=0)
    sync_duration_seconds = Column(Integer, default=0)
    status = Column(String(20), default='running', index=True)
    error_message = Column(Text)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    def __repr__(self):
        return f"<SyncStatus(type={self.sync_type}, status={self.status}, last_sync={self.last_sync_time})>"

class ApiLog(Base):
    __tablename__ = 'api_logs'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    endpoint = Column(String(100), index=True)
    method = Column(String(10))
    status_code = Column(Integer, index=True)
    response_time_ms = Column(Integer)
    request_size_bytes = Column(Integer)
    response_size_bytes = Column(Integer)
    error_message = Column(Text)
    created_at = Column(DateTime, default=datetime.utcnow, index=True)
    
    def __repr__(self):
        return f"<ApiLog(endpoint={self.endpoint}, status={self.status_code}, time={self.response_time_ms}ms)>"

# Database connection class
class DatabaseManager:
    def __init__(self, database_url):
        self.engine = create_engine(database_url, echo=False)
        self.SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=self.engine)
    
    def create_tables(self):
        """Create all tables"""
        Base.metadata.create_all(bind=self.engine)
    
    def get_session(self):
        """Get database session"""
        return self.SessionLocal()
    
    def close(self):
        """Close database connection"""
        self.engine.dispose()

# Utility functions
def parse_zoho_datetime(date_string):
    """Parse Zoho datetime string to Python datetime"""
    if not date_string:
        return None
    
    try:
        # Handle different Zoho datetime formats
        if 'T' in date_string:
            if date_string.endswith('Z'):
                return datetime.fromisoformat(date_string.replace('Z', '+00:00'))
            else:
                return datetime.fromisoformat(date_string)
        else:
            return datetime.strptime(date_string, '%Y-%m-%d')
    except Exception as e:
        print(f"Error parsing datetime '{date_string}': {e}")
        return None

def calculate_processing_time(threads):
    """Calculate processing time between threads"""
    if not threads or len(threads) < 2:
        return None
    
    try:
        # Separate incoming and outgoing threads
        incoming = [t for t in threads if t.get('direction') == 'in']
        outgoing = [t for t in threads if t.get('direction') == 'out']
        
        if not incoming or not outgoing:
            return None
        
        # Sort by created time
        incoming.sort(key=lambda x: x.get('createdTime', ''))
        outgoing.sort(key=lambda x: x.get('createdTime', ''))
        
        last_outgoing = outgoing[-1]
        last_incoming = incoming[-1]
        
        outgoing_time = parse_zoho_datetime(last_outgoing.get('createdTime'))
        incoming_time = parse_zoho_datetime(last_incoming.get('createdTime'))
        
        if outgoing_time and incoming_time and incoming_time > outgoing_time:
            diff = incoming_time - outgoing_time
            days = diff.days
            hours, remainder = divmod(diff.seconds, 3600)
            minutes, seconds = divmod(remainder, 60)
            
            if days > 0:
                return f"{days}d {hours}h {minutes}m"
            elif hours > 0:
                return f"{hours}h {minutes}m"
            else:
                return f"{minutes}m {seconds}s"
        
        return None
    except Exception as e:
        print(f"Error calculating processing time: {e}")
        return None

if __name__ == "__main__":
    # Test database connection
    from config import DatabaseConfig
    
    db_config = DatabaseConfig()
    db_manager = DatabaseManager(db_config.DATABASE_URL)
    
    try:
        db_manager.create_tables()
        print("Database tables created successfully!")
        
        # Test session
        session = db_manager.get_session()
        print("Database connection successful!")
        session.close()
        
    except Exception as e:
        print(f"Database error: {e}")
    finally:
        db_manager.close()


