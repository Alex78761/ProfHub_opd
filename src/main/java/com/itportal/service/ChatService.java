package com.itportal.service;

import com.itportal.model.Chat;
import com.itportal.model.Message;
import com.itportal.model.ChatStatus;
import com.itportal.model.User;
import com.itportal.repository.ChatRepository;
import com.itportal.repository.MessageRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.util.List;

@Service
public class ChatService {

    private final ChatRepository chatRepository;
    private final MessageRepository messageRepository;

    public ChatService(ChatRepository chatRepository, MessageRepository messageRepository) {
        this.chatRepository = chatRepository;
        this.messageRepository = messageRepository;
    }

    @Transactional
    public Chat createChat(Chat chat) {
        chat.setStatus(ChatStatus.WAITING);
        return chatRepository.save(chat);
    }

    public Chat getChat(Long id) {
        return chatRepository.findById(id)
            .orElseThrow(() -> new RuntimeException("Чат не найден"));
    }

    @Transactional
    public Message addMessage(Long chatId, Message message) {
        Chat chat = getChat(chatId);
        message.setChat(chat);
        Message savedMessage = messageRepository.save(message);
        chat.getMessages().add(savedMessage);
        return savedMessage;
    }

    public List<Chat> getAvailableChats() {
        return chatRepository.findByStatusAndConsultantIsNull(ChatStatus.WAITING);
    }

    public List<Chat> getUserChats(User user) {
        return chatRepository.findByUserOrderByCreatedAtDesc(user);
    }

    public List<Chat> getConsultantChats(User consultant) {
        return chatRepository.findByConsultantOrderByCreatedAtDesc(consultant);
    }

    @Transactional
    public Chat assignConsultant(Chat chat, User consultant) {
        chat.setConsultant(consultant);
        chat.setStatus(ChatStatus.ACTIVE);
        return chatRepository.save(chat);
    }

    @Transactional
    public void closeChat(Long chatId) {
        Chat chat = getChat(chatId);
        chat.setStatus(ChatStatus.CLOSED);
        chatRepository.save(chat);
    }
} 