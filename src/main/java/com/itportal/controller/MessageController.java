package com.itportal.controller;

import com.itportal.model.Chat;
import com.itportal.model.Message;
import com.itportal.model.User;
import com.itportal.service.ChatService;
import com.itportal.service.MessageService;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.*;

@Controller
@RequestMapping("/messages")
public class MessageController {

    private final MessageService messageService;
    private final ChatService chatService;

    public MessageController(MessageService messageService, ChatService chatService) {
        this.messageService = messageService;
        this.chatService = chatService;
    }

    @PostMapping("/send/{chatId}")
    @ResponseBody
    public Message sendMessage(
            @PathVariable Long chatId,
            @RequestParam String content,
            @AuthenticationPrincipal User user) {
        
        Chat chat = chatService.getChat(chatId);
        
        // Проверяем, что пользователь имеет доступ к чату
        if (!chat.getUser().equals(user) && !chat.getConsultant().equals(user)) {
            throw new RuntimeException("У вас нет доступа к этому чату");
        }

        Message message = new Message();
        message.setContent(content);
        message.setUser(user);
        message.setChat(chat);
        
        return messageService.saveMessage(message);
    }

    @GetMapping("/{chatId}")
    @ResponseBody
    public Iterable<Message> getMessages(
            @PathVariable Long chatId,
            @AuthenticationPrincipal User user) {
        
        Chat chat = chatService.getChat(chatId);
        
        // Проверяем, что пользователь имеет доступ к чату
        if (!chat.getUser().equals(user) && !chat.getConsultant().equals(user)) {
            throw new RuntimeException("У вас нет доступа к этому чату");
        }
        
        return messageService.getChatMessages(chatId);
    }
} 