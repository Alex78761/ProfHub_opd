package com.itportal.repository;

import com.itportal.model.Chat;
import com.itportal.model.ChatStatus;
import com.itportal.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface ChatRepository extends JpaRepository<Chat, Long> {
    List<Chat> findByStatusAndConsultantIsNull(ChatStatus status);
    List<Chat> findByUserOrderByCreatedAtDesc(User user);
    List<Chat> findByConsultantOrderByCreatedAtDesc(User consultant);
} 