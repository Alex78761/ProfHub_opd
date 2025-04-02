package com.itportal.service;

import com.itportal.model.Message;
import com.itportal.repository.MessageRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

@Service
public class MessageService {

    private final MessageRepository messageRepository;

    public MessageService(MessageRepository messageRepository) {
        this.messageRepository = messageRepository;
    }

    @Transactional
    public Message saveMessage(Message message) {
        return messageRepository.save(message);
    }

    public List<Message> getChatMessages(Long chatId) {
        return messageRepository.findByChatIdOrderByCreatedAtAsc(chatId);
    }

    public Message getMessage(Long id) {
        return messageRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Сообщение не найдено"));
    }
} 