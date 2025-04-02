package com.itportal.controller;

import com.itportal.model.Chat;
import com.itportal.model.Message;
import com.itportal.model.User;
import com.itportal.service.ChatService;
import com.itportal.service.UserService;
import org.springframework.security.access.prepost.PreAuthorize;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@Controller
@RequestMapping("/consultant")
@PreAuthorize("hasRole('CONSULTANT')")
public class ConsultantController {

    private final ChatService chatService;
    private final UserService userService;

    public ConsultantController(ChatService chatService, UserService userService) {
        this.chatService = chatService;
        this.userService = userService;
    }

    @GetMapping("/chats")
    public String listChats(Model model, @AuthenticationPrincipal User consultant) {
        List<Chat> availableChats = chatService.getAvailableChats();
        List<Chat> activeChats = chatService.getConsultantChats(consultant);
        
        model.addAttribute("availableChats", availableChats);
        model.addAttribute("activeChats", activeChats);
        return "consultant/chats";
    }

    @GetMapping("/chat/{id}")
    public String viewChat(@PathVariable Long id, Model model, @AuthenticationPrincipal User consultant) {
        Chat chat = chatService.getChat(id);
        
        // Если чат еще не назначен консультанту, назначаем текущего
        if (chat.getConsultant() == null) {
            chat = chatService.assignConsultant(chat, consultant);
        }
        
        model.addAttribute("chat", chat);
        model.addAttribute("newMessage", new Message());
        return "consultant/chat";
    }

    @PostMapping("/chat/{id}/message")
    public String sendMessage(@PathVariable Long id, 
                            @ModelAttribute Message message,
                            @AuthenticationPrincipal User consultant) {
        message.setUser(consultant);
        chatService.addMessage(id, message);
        return "redirect:/consultant/chat/" + id;
    }

    @PostMapping("/chat/{id}/close")
    public String closeChat(@PathVariable Long id) {
        chatService.closeChat(id);
        return "redirect:/consultant/chats";
    }

    @GetMapping("/profile")
    public String viewProfile(Model model, @AuthenticationPrincipal User consultant) {
        model.addAttribute("consultant", consultant);
        return "consultant/profile";
    }
} 