package com.itportal.controller;

import com.itportal.model.Chat;
import com.itportal.model.Message;
import com.itportal.model.User;
import com.itportal.model.ChatStatus;
import com.itportal.service.ChatService;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@Controller
@RequestMapping("/chat")
public class ChatController {

    private final ChatService chatService;

    public ChatController(ChatService chatService) {
        this.chatService = chatService;
    }

    @GetMapping("/start")
    public String startChat(@AuthenticationPrincipal User user) {
        Chat chat = new Chat();
        chat.setUser(user);
        chat.setStatus(ChatStatus.WAITING);
        Chat savedChat = chatService.createChat(chat);
        return "redirect:/chat/" + savedChat.getId();
    }

    @GetMapping("/{id}")
    public String viewChat(@PathVariable Long id, Model model, @AuthenticationPrincipal User user) {
        Chat chat = chatService.getChat(id);
        
        // Проверяем, что пользователь имеет доступ к чату
        if (!chat.getUser().equals(user)) {
            return "redirect:/";
        }
        
        model.addAttribute("chat", chat);
        model.addAttribute("newMessage", new Message());
        return "chat/view";
    }

    @PostMapping("/{id}/message")
    public String sendMessage(@PathVariable Long id, 
                            @ModelAttribute Message message,
                            @AuthenticationPrincipal User user) {
        Chat chat = chatService.getChat(id);
        
        // Проверяем, что пользователь имеет доступ к чату
        if (!chat.getUser().equals(user)) {
            return "redirect:/";
        }
        
        message.setUser(user);
        chatService.addMessage(id, message);
        return "redirect:/chat/" + id;
    }

    @GetMapping("/list")
    public String listChats(Model model, @AuthenticationPrincipal User user) {
        List<Chat> chats = chatService.getUserChats(user);
        model.addAttribute("chats", chats);
        return "chat/list";
    }

    @PostMapping("/{id}/close")
    public String closeChat(@PathVariable Long id, @AuthenticationPrincipal User user) {
        chatService.closeChat(id);
        return "redirect:/chat/list";
    }
} 